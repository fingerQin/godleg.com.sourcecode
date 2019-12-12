<?php
/**
 * 签到业务。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use Utils\YCore;
use Utils\YCache;
use Models\GoldConsume;
use Services\Gold\Gold;

class Checkin extends \Services\AbstractBase
{
    /**
     * 签到赠送的金币数量。
     */
    const CHECK_IN_GOLD = 10;

    /**
     * 签到。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return array
     */
    public static function do($userid)
    {
        $time     = time();
        $date     = date('Y-m-d', $time);
        $cacheKey = "checkin:{$userid}:{$date}";
        $redis    = YCache::getRedisClient();
        $status   = $redis->set($cacheKey, $date, ['NX', 'EX' => 86400]);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '请勿重复签到!');
        }
        $where = [
            'userid'       => $userid,
            'consume_code' => GoldConsume::CONSUME_CODE_CHECK_IN,
            'c_time'       => ['BETWEEN', ["{$date} 00:00:00", "{$date} 23:59:59"]]
        ];
        $GoldConsumeModel = new GoldConsume();
        $checkin = $GoldConsumeModel->fetchOne([], $where);
        if ($checkin) {
            YCore::exception(STATUS_SERVER_ERROR, '请勿重复签到!');
        }
        $gold = Gold::consume($userid, self::CHECK_IN_GOLD, GoldConsume::CONSUME_TYPE_ADD, GoldConsume::CONSUME_CODE_CHECK_IN);
        return [
            'gold' => $gold, // 当前用户金币。
            'add'  => self::CHECK_IN_GOLD // 增加的金币。
        ];
    }

    /**
     * 是否已经签到。
     *
     * @param  int  $userid  用户 ID。
     * @return bool
     */
    public static function isCheckIn($userid)
    {
        $time        = time();
        $date        = date('Y-m-d', $time);
        $cacheKey    = "checkin:{$userid}:{$date}";
        $checkInDate = YCache::get($cacheKey);
        if ($checkInDate != false && $checkInDate == $date) {
            return true;
        }
        $where = [
            'userid'       => $userid,
            'consume_code' => GoldConsume::CONSUME_CODE_CHECK_IN,
            'c_time'       => ['BETWEEN', ["{$date} 00:00:00", "{$date} 23:59:59"]]
        ];
        $GoldConsumeModel = new GoldConsume();
        $checkin = $GoldConsumeModel->fetchOne([], $where);
        if ($checkin) {
            return true;
        } else {
            return false;
        }
    }
}