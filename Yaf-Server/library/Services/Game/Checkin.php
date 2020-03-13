<?php
/**
 * 签到业务。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Cache;
use finger\Core;
use finger\Database\Db;
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
        $time      = time();
        $date      = date('Y-m-d', $time);
        $cacheKey  = "checkin:{$userid}:{$date}";
        $redis     = Cache::getRedisClient();
        $datatime  = date('Y-m-d 23:59:59', time());
        $timestamp = strtotime($datatime);
        $lockTime  = $timestamp - time() + 60; // 当前截止凌晨剩余的时间。为了避免凌晨临界值问题，增加 60 秒。
        $status    = $redis->set($cacheKey, $date, ['NX', 'EX' => $lockTime]);
        if (!$status) {
            Core::exception(STATUS_SERVER_ERROR, '请勿重复签到!');
        }
        $where = [
            'userid'       => $userid,
            'consume_code' => GoldConsume::CONSUME_CODE_CHECK_IN,
            'c_time'       => ['BETWEEN', ["{$date} 00:00:00", "{$date} 23:59:59"]]
        ];
        $GoldConsumeModel = new GoldConsume();
        $checkin = $GoldConsumeModel->fetchOne([], $where);
        if ($checkin) {
            Core::exception(STATUS_SERVER_ERROR, '请勿重复签到!');
        }
        try {
            Db::beginTransaction();
            $gold = Gold::consume($userid, self::CHECK_IN_GOLD, GoldConsume::CONSUME_TYPE_ADD, GoldConsume::CONSUME_CODE_CHECK_IN);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            $redis->del($cacheKey); // 如果签到失败则需要删除 Redis lock。
        }
        return [
            'gold' => $gold,                // 当前用户金币。
            'add'  => self::CHECK_IN_GOLD   // 增加的金币。
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
        $checkInDate = Cache::get($cacheKey);
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

    /**
     * 用户签到详情信息。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return array
     */
    public static function detail($userid)
    {
        $accumulativeCheckInTotal = self::userCheckInTotal($userid);
        $latestSevenDayRecords    = self::latestSevenDayRecords($userid);
        return [
            'total'   => $accumulativeCheckInTotal,
            'records' => $latestSevenDayRecords
        ];
    }

    /**
     * 用户签到总次数。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return int
     */
    protected static function userCheckInTotal($userid)
    {
        $GoldConsumeModel = new GoldConsume();
        return $GoldConsumeModel->count(['userid' => $userid], $isMaster = true);
    }

    /**
     * 最近七天签到记录。
     *
     * @param  int  $userid  用户 ID。
     *
     * @return array
     */
    protected static function latestSevenDayRecords($userid)
    {
        $startTime = date('Y-m-d 00:00:00', strtotime('-7 day'));
        $endTime   = date('Y-m-d 23:59:59', time());
        $GoldConsumeModel = new GoldConsume();
        $where = [
            'userid'       => $userid,
            'consume_code' => GoldConsume::CONSUME_CODE_CHECK_IN,
            'consume_type' => GoldConsume::CONSUME_TYPE_ADD,
            'c_time'       => ['BETWEEN', [$startTime, $endTime]]
        ];
        $columns = ['gold', 'c_time'];
        $result  = $GoldConsumeModel->fetchAll($columns, $where, 7, 'c_time ASC');
        $checkInResult = [];
        $latestSevenDayDataArr = self::latestSevenDayDate();
        foreach ($latestSevenDayDataArr as $date) {
            $dateKey = date('n.d', strtotime($date));
            $checkInResult[$dateKey] = ['date' => $dateKey, 'gold' => 0, 'status' => 0]; // status 0 未签到、1 已签到。
            foreach ($result as $value) {
                if (substr($value['c_time'], 0, 10) == $date) {
                    $checkInResult[$dateKey] = ['date' => $dateKey, 'gold' => $value['gold'], 'status' => 1];
                }
            }
        }
        return $checkInResult;
    }

    /**
     * 获取最近一周的日期数组。
     *
     * @return array
     */
    protected static function latestSevenDayDate()
    {
        return [
            date('Y-m-d', strtotime('-6 day')),
            date('Y-m-d', strtotime('-5 day')),
            date('Y-m-d', strtotime('-4 day')),
            date('Y-m-d', strtotime('-3 day')),
            date('Y-m-d', strtotime('-2 day')),
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d', time())
        ];
    }
}