<?php
/**
 * 猜猜猜游戏。
 * @author fingerQin
 * @date 2019-08-08
 * @todo 谜题详情页 URL 未实现、谜题分享统计未实现。
 */

namespace Services\Game;

use finger\Cache;
use finger\Core;
use finger\Database\Db;
use finger\Url;
use Models\GmRiddle;

class Riddle extends \Services\AbstractBase
{
    /**
     * 表数据缓存 Redis KEY。
     * -- 保存根据优先级分组的谜题库 OpenID。
     */
    const RIDDLE_KEY = 'riddle_key';

    /**
     * 单谜题缓存 KEY 前缀。
     */
    const RIDDLE_PRE_KEY = 'riddle:';

    /**
     * 谜题列表。
     *
     * @param  int  $score  分值。大于等于此分值。
     * @param  int  $page   页码。
     * @param  int  $count  每页显示条数。
     *
     * @return array
     */
    public static function list($score = 0, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM finger_riddle ';
        $columns   = ' openid, score, question, question_img, answer, answer_img';
        $where     = ' WHERE source = :source ';
        $params    = [
            ':source' => GmRiddle::SOURCE_SYSTEM
        ];
        if ($score > 0) {
            $where .= " AND score = :score ";
            $params[':score'] = $score;
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 根据题目 OpenID 取谜题。
     *
     * @param  string  $questipnOpenID  谜题 OpenID。
     * @param  int     $userid          用户 ID(当前仅作统计用)。
     *
     * @return array
     */
    public static function make($questipnOpenID, $userid)
    {
        $detail = self::detail($questipnOpenID);
        return [
            'openid'       => $detail['openid'],
            'score'        => $detail['score'],
            'question'     => $detail['question'],
            'question_img' => $detail['question_img'],
            'answer'       => $detail['answer'],
            'answer_img'   => $detail['answer_img'],
            'view_url'     => self::url($detail['openid'])
        ];
    }

    /**
     * 随机取一条谜题。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return array
     */
    public static function randomMake($userid)
    {
        $configCache = self::getPriorityCache();
        $priority    = self::getRandPriority();
        $dicts       = isset($configCache[$priority]) ? $configCache[$priority] : [];
        if (empty($dicts)) {
            Core::exception(STATUS_ERROR, '配置数据缺失!');
        }
        $count  = count($dicts);
        $randV  = mt_rand(0, $count-1);
        $detail = self::detail($dicts[$randV]);
        return [
            'openid'       => $detail['openid'],
            'score'        => $detail['score'],
            'question'     => $detail['question'],
            'question_img' => $detail['question_img'],
            'answer'       => $detail['answer'],
            'answer_img'   => $detail['answer_img'],
            'view_url'     => self::url($detail['openid'])
        ];
    }

    /**
     * 获取谜题详情。
     *
     * @param  string  $questipnOpenID  谜题 OpenID。
     *
     * @return array
     */
    public static function detail($questipnOpenID)
    {
        $redis  = Cache::getRedisClient();
        $key    = self::RIDDLE_PRE_KEY . $questipnOpenID;
        $detail = $redis->get($key);
        if ($detail) {
            return json_decode($detail, true);
        } else {
            $columns     = ['openid', 'score', 'question', 'question_img', 'answer', 'answer_img'];
            $RiddleModel = new GmRiddle();
            $detail      = $RiddleModel->fetchOne($columns, ['openid' => $questipnOpenID]);
            if (empty($detail)) {
                Core::exception(STATUS_SERVER_ERROR, '谜题丢失了~');
            }
            $redis->set($key, json_encode($detail, JSON_UNESCAPED_UNICODE));
            return $detail;
        }
    }

    /**
     * 清除单道谜题缓存。
     * 
     * -- 提供给管理后台调用。
     *
     * @param  string  $questipnOpenID  问题 OepnID。
     *
     * @return void
     */
    public static function clearDetailCache($questipnOpenID)
    {
        $redis = Cache::getRedisClient();
        $key   = self::RIDDLE_PRE_KEY . $questipnOpenID;
        $redis->del($key);
    }

    /**
     * 谜题缓存重置。
     * 
     * -- 提供给管理后台调用。
     *
     * @return void
     */
    public static function resetCache()
    {
        $redis = Cache::getRedisClient();
        $redis->del(self::RIDDLE_KEY);
        self::getPriorityCache();
    }

    /**
     * 根据谜题 OpenID 生成对应的查看页 URL。
     *
     * @param  string  $questipnOpenID  谜题 OpenID。
     *
     * @return string
     */
    protected static function url($questipnOpenID)
    {
        // return Url::h5Url('Game', 'riddle', ['openid' => $questipnOpenID]);
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
     * 获取按优先级分组后的谜题 OpenID 缓存。
     *
     * @return array
     */
    protected static function getPriorityCache()
    {
        $redis     = Cache::getRedisClient();
        $dictCache = $redis->get(self::RIDDLE_KEY);
        if ($dictCache) {
            $dicts = json_decode($dictCache, true);
        } else {
            $columns     = ['priority', 'openid'];
            $RiddleModel = new GmRiddle();
            $result      = $RiddleModel->fetchAll($columns, ['source' => GmRiddle::SOURCE_SYSTEM]);
            $dicts       = [];
            foreach ($result as $item) {
                $dicts[$item['priority']][] = $item['openid']; // 按照优先级进行拆分。
            }
            $redis->set(self::RIDDLE_KEY, json_encode($dicts, JSON_UNESCAPED_UNICODE));
        }
        return $dicts;
    }
}