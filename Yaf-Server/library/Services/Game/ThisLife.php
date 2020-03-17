<?php
/**
 * 测今生。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Cache;
use finger\Core;
use Models\GmThisLife;

class ThisLife extends \Services\AbstractBase
{
    /**
     * 表数据缓存 Redis KEY。
     */
    const PRE_LIFE_KEY = 'this_life_key';

    /**
     * 运算。
     *
     * @param  string  $name  姓名。
     *
     * @return array
     */
    public static function do($name)
    {
        $configCache = self::getConfigCache();
        $priority    = self::getRandPriority();
        $dicts       = isset($configCache[$priority]) ? $configCache[$priority] : [];
        if (empty($dicts)) {
            Core::exception(STATUS_ERROR, '配置数据缺失!');
        }
        $count = count($dicts);
        $randV = mt_rand(0, $count-1);
        $dict  = $dicts[$randV];
        return [
            'name'  => $name,
            'title' => $dict['title'],
            'intro' => $dict['intro']
        ];
    }

    /**
     * 缓存重置。
     * 
     * -- 提供给管理后台使用。
     *
     * @return void
     */
    public static function resetCache()
    {
        $redis = Cache::getRedisClient();
        $redis->del(self::PRE_LIFE_KEY);
        self::getConfigCache();
    }

    /**
     * 获取一个随机的优先级。
     * 
     * -- 优先级根据比例进行给出。
     *
     * @return int
     */
    protected static function getRandPriority()
    {
        $randValue = mt_rand(1, 1000);
        if ($randValue <= 500) { // 高优先级。
            return 1;
        } else if ($randValue > 990) { // 低优先级。
            return 3;
        } else { // 中优先级别。
            return 2;
        }
    }

    /**
     * 读取配置表缓存的数据。
     *
     * @return array
     */
    protected static function getConfigCache()
    {
        $redis     = Cache::getRedisClient();
        $dictCache = $redis->get(self::PRE_LIFE_KEY);
        if ($dictCache) {
            $dicts = json_decode($dictCache, true);
        } else {
            $columns       = ['priority', 'score', 'title', 'intro'];
            $GmThisLife = new GmThisLife();
            $result        = $GmThisLife->fetchAll($columns);
            $dicts         = [];
            foreach ($result as $item) {
                $dicts[$item['priority']][] = $item; // 按照优先级进行拆分。
            }
            $redis->set(self::PRE_LIFE_KEY, json_encode($dicts, JSON_UNESCAPED_UNICODE));
        }
        return $dicts;
    }
}