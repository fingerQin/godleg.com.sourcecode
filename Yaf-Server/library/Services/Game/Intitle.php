<?php
/**
 * 取名。
 * --1 单字为展示型。告知用户用户姓名常用的名。
 * --2 双字为随机取名用。
 * @author fingerQin
 * @date 2019-08-08
 * @todo 增加统计：经纬度、IP、姓氏、时间、每个名字随机出来的概率。
 */

namespace Services\Game;

use Utils\YCache;
use Utils\YCore;
use Models\GmNameDict;

class Intitle extends \Services\AbstractBase
{
    /**
     * Redis 缓存 KEY。
     * -- 缓存了姓名字典表所有的数据。
     */
    const NAME_DICT_KEY = 'name_dict_key';

    /**
     * 运算取名。
     *
     * @param  string  $familyName  姓氏。
     * @param  string  $sex         性别：female-女、male-男。
     *
     * @return array
     */
    public static function do($familyName, $sex)
    {
        $dictsCache = self::getNameDictCache();
        $dicts      = ($sex == GmNameDict::SEX_FEMALE) ? $dictsCache['female_dict'] : $dictsCache['male_dict'];
        if (empty($dicts)) {
            YCore::exception(STATUS_ERROR, '字典数据缺失!');
        }
        $count     = count($dicts);
        $randValue = mt_rand(0, $count-1);
        $dict      = $dicts[$randValue];
        return [
            'name' => "{$familyName}{$dict['name']}",
            'sex'  => $dict['sex'],
            'expl' => $dict['expl']
        ];
    }

    /**
     * 取名常用词。
     *
     * @return array
     */
    public static function hotWorld()
    {
        $dictsCache = self::getNameDictCache();
        return [
            'female' => $dictsCache['female_hot'],
            'male'   => $dictsCache['male_hot'],
        ];
    }

    /**
     * 重置缓存。
     * 
     * -- 提供给管理后台调用。
     *
     * @return void
     */
    public static function resetCache()
    {
        $redis = YCache::getRedisClient();
        $redis->del(self::NAME_DICT_KEY);
        self::getNameDictCache();
    }

    /**
     * 读取姓名字典表缓存的数据。
     *
     * @return array
     * 
     * --
     * return [
     *     'female_hot'  => [], // 女孩取名常用字。
     *     'male_hot'    => [], // 男孩取名常用字。
     *     'female_dict' => [], // 女孩常用名字字典。
     *     'male_dict'   => [], // 男孩常用名字字典。
     * ];
     * --
     */
    protected static function getNameDictCache()
    {
        $redis     = YCache::getRedisClient();
        $dictCache = $redis->get(self::NAME_DICT_KEY);
        if ($dictCache) {
            $dicts = json_decode($dictCache, true);
        } else {
            $columns       = ['name', 'type', 'sex', 'expl'];
            $NameDictModel = new GmNameDict();
            $result        = $NameDictModel->fetchAll($columns);
            $dicts         = [
                'female_hot'  => self::getFemaleHot($result),
                'male_hot'    => self::getMaleHot($result),
                'female_dict' => self::getFemaleDict($result),
                'male_dict'   => self::getMaleDict($result)
            ];
            $redis->set(self::NAME_DICT_KEY, json_encode($dicts, JSON_UNESCAPED_UNICODE));
        }
        return $dicts;
    }

    /**
     * 获取女孩取名常用字
     *
     * @param  array  &$result  姓名库结果集。
     *
     * @return array
     */
    protected static function getFemaleHot(&$result)
    {
        $femaleHotResult = [];
        foreach ($result as $item) {
            if ($item['type'] == GmNameDict::TYPE_SINGLE && $item['sex'] == GmNameDict::SEX_FEMALE) {
                $femaleHotResult[] = $item;
            }
        }
        return $femaleHotResult;
    }

    /**
     * 获取男孩取名常用字
     *
     * @param  array  &$result  姓名库结果集。
     *
     * @return array
     */
    protected static function getMaleHot(&$result)
    {
        $maleHotResult = [];
        foreach ($result as $item) {
            if ($item['type'] == GmNameDict::TYPE_SINGLE && $item['sex'] == GmNameDict::SEX_MALE) {
                $maleHotResult[] = $item;
            }
        }
        return $maleHotResult;
    }

    /**
     * 获取女孩常用名字字典。
     *
     * @param  array  &$result  姓名库结果集。
     *
     * @return array
     */
    protected static function getFemaleDict(&$result)
    {
        $femaleHotResult = [];
        foreach ($result as $item) {
            if ($item['type'] == GmNameDict::TYPE_DOUBLE && $item['sex'] == GmNameDict::SEX_FEMALE) {
                $femaleHotResult[] = $item;
            }
        }
        return $femaleHotResult;
    }

    /**
     * 获取男孩常用名字字典。
     *
     * @param  array  &$result  姓名库结果集。
     *
     * @return array
     */
    protected static function getMaleDict(&$result)
    {
        $maleHotResult = [];
        foreach ($result as $item) {
            if ($item['type'] == GmNameDict::TYPE_DOUBLE && $item['sex'] == GmNameDict::SEX_MALE) {
                $maleHotResult[] = $item;
            }
        }
        return $maleHotResult;
    }
}