<?php
/**
 * 打卡任务业务封装。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Services\Task;

use Utils\YCache;
use Utils\YCore;
use finger\Database\Db;
use finger\RedisMutexLock;
use Models\TaskSponsor;
use Models\Task as TaskModel;
use Models\TaskRecord;
use Services\Gold\Gold;

class Task extends \Services\AbstractBase
{
    /**
     * 打卡记录。
     * 
     * -- 只显示最近 3 个月的打卡记录。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $page    页码。
     * @param  int  $count   每页条数。
     *
     * @return array
     */
    public static function record($userid, $page = 1, $count = 20)
    {
        $threeMonthAgo = time() - 90 * 24 * 60 * 60;
        $threeMonthAgo = date('Y-m-d 00:00:00', $threeMonthAgo);
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM finger_task_record AS a INNER JOIN finger_task AS b ON(a.taskid=b.taskid) ';
        $columns   = ' a.id, a.taskid, a.gold, a.step_count, a.image_url, a.longitude, a.latitude, a.c_time, b.task_name ';
        $where     = ' WHERE a.userid = :userid AND a.c_time > :startTime ';
        $params    = [
            ':userid'    => $userid,
            ':startTime' => $threeMonthAgo
        ];
        $orderBy = "ORDER BY a.id DESC";
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        $result  = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 打卡任务列表。
     *
     * @param  string  $districtCode  区/县地区编码。
     * @param  int     $page          页码。
     * @param  int     $count         每页条数。
     *
     * @return array
     */
    public static function lists($districtCode, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM finger_task AS a INNER JOIN finger_task_sponsor AS b ON(a.sponsorid=b.sponsorid) ';
        $columns   = ' a.taskid, a.task_name, a.sponsorid, a.address, a.gold, a.move_step, a.everyday_times, a.total_times, '
                   . ' a.albums, a.longitude, a.latitude, a.start_time, a.end_time, b.name ';
        $where     = ' WHERE a.status = :status AND a.district_code = :district_code AND a.display = :display ';
        $params    = [
            ':status'        => TaskSponsor::STATUS_YES,
            ':display'       => TaskSponsor::STATUS_YES,
            ':district_code' => $districtCode
        ];
        $orderBy  = "ORDER BY a.taskid DESC";
        $sql      = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total    = Db::count($sql, $params);
        $sql      = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list     = Db::all($sql, $params);
        $datetime = date('Y-m-d H:i:s', time());
        foreach ($list as $key => $item) {
            $taskStatus = self::taskTimeStatus($datetime, $item['start_time'], $item['end_time']);
            unset($item['start_time'], $item['end_time']);
            $item = array_merge($item, $taskStatus);
            $list[$key] = $item;
        }
        $result   = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 任务活动时间状态。
     *
     * @param  string  $currentTime  当前时间。
     * @param  string  $startTime    任务开始时间。
     * @param  string  $endTime      任务截止时间。
     *
     * @return array
     */
    private static function taskTimeStatus($currentTime, $startTime, $endTime)
    {
        if ($currentTime > $startTime && $currentTime < $endTime) {
            return [
                'task_status'     => 1,
                'task_status_txt' => '进行中'
            ];
        } else if ($currentTime < $startTime) {
            return [
                'task_status'     => 2,
                'task_status_txt' => '未开始'
            ]; 
        } else if ($currentTime > $endTime) {
            return [
                'task_status'     => 3,
                'task_status_txt' => '已结束'
            ];
        }
    }

    /**
     * 打卡。
     *
     * @param  int     $taskId     任务 ID。
     * @param  int     $userid     用户 ID。
     * @param  int     $stepCount  步数。
     * @param  int     $longitude  经度。
     * @param  int     $latitude   纬度。
     * @param  string  $imageUrl   签到图片。
     * @param  string  $ip         签到时所在 IP。
     *
     * @return array
     */
    public static function do($taskId, $userid, $stepCount, $longitude, $latitude, $imageUrl = '', $ip = '')
    {
        $where = [
            'taskid'  => $taskId,
            'display' => TaskModel::STATUS_YES,
            'status'  => TaskModel::STATUS_YES
        ];
        $columns   = 'taskid,sponsorid,district_code,gold,move_step,times_limit,'
                   . 'longitude,latitude,everyday_times,total_times,start_time,end_time';
        $TaskModel = new TaskModel();
        $task = $TaskModel->fetchOne($columns, $where);
        if (empty($task)) {
            YCore::exception(STATUS_SERVER_ERROR, '打卡任务不存在或已经删除');
        }
        self::checkTaskMoveStep($stepCount, $task['move_step']);
        self::checkTaskCheckInAddressDistance($task['longitude'], $task['latitude'], $longitude, $latitude);
        self::checkTaskValidTime($task['start_time'], $task['end_time']);
        self::checkTaskEverydayUpperLimit($taskId, $task['everyday_times']);
        self::checkTaskUpperLimit($taskId, $task['total_times']);
        self::checkUserTodayIsCheckIn($userid, $taskId);
        self::checkUserCheckInTimes($userid, $taskId, $task['times_limit']);
        Db::beginTransaction();
        $data = [
            'userid'     => $userid,
            'sponsorid'  => $task['sponsorid'],
            'taskid'     => $taskId,
            'gold'       => $task['gold'],
            'step_count' => $stepCount,
            'image_url'  => $imageUrl,
            'longitude'  => $longitude,
            'latitude'   => $latitude,
            'c_time'     => date('Y-m-d H:i:s', time())
        ];
        $TaskRecordModel = new TaskRecord();
        $ok = $TaskRecordModel->insert($data);
        if (!$ok) {
            Db::rollBack();
            YCore::exception(STATUS_SERVER_ERROR, '增加失败');
        }
        self::addGold($userid, $task['gold']);
        self::setTodayAlreadyDo($userid, $taskId);
        Db::commit();
    }

    /**
     * 验证任务运动步数是否达标。
     *
     * @param  int  $userMoveStep  用户运动步数。
     * @param  int  $taskMoveStep  任务运动步数。
     *
     * @return void
     */
    private static function checkTaskMoveStep($userMoveStep, $taskMoveStep)
    {
        if ($userMoveStep < $taskMoveStep) {
            YCore::exception(STATUS_SERVER_ERROR, "今日运动步数不足{$taskMoveStep}步");
        }
    }

    /**
     * 验证任务打卡地点与用户打卡位置是否在范围内。
     *
     * @param  float  $taskLong   打卡任务经度。
     * @param  float  $taskLat    打卡任务纬度。
     * @param  float  $longitude  用户位置经度。
     * @param  float  $latitude   用户位置纬度。
     *
     * @return void
     */
    private static function checkTaskCheckInAddressDistance($taskLong, $taskLat, $longitude, $latitude)
    {
        $distance = self::distance($taskLong, $taskLat, $longitude, $latitude);
        if ($distance > 50) {
            YCore::exception(STATUS_SERVER_ERROR, '不在打卡地点范围内');
        }
    }

    /**
     * 验证打卡动作是否在任务参与时间内。
     *
     * @param  string  $startTime  生效时间。
     * @param  string  $endTime    失效时间。
     *
     * @return void
     */
    private static function checkTaskValidTime($startTime, $endTime)
    {
        $datetime = date('Y-m-d H:i:s', time());
        if ($datetime < $startTime) {
            YCore::exception(STATUS_SERVER_ERROR, '打卡任务未开始');
        }
        if ($datetime > $endTime) {
            YCore::exception(STATUS_SERVER_ERROR, '打卡任务已经结束');
        }
    }

    /**
     * 验证任务每日打卡是否已达总上限。
     * 
     * @param  int  $taskId         任务 ID。
     * @param  int  $everydayTimes  每天日打卡上限。
     * 
     * @return void
     */
    private static function checkTaskEverydayUpperLimit($taskId, $everydayTimes)
    {
        $taskTodayCount = self::getTodayTaskCount($taskId);
        if ($everydayTimes > 0 && $taskTodayCount >= $everydayTimes) {
            YCore::exception(STATUS_SERVER_ERROR, '今日参与次数已满');
        }
    }

    /**
     * 验证指定打卡任务当前签到是否已达总上限。
     *
     * @param  int  $taskId      任务 ID。
     * @param  int  $totalLimit  打卡任务上限值。
     *
     * @return void
     */
    private static function checkTaskUpperLimit($taskId, $totalLimit)
    {
        $taskTotal = self::getTaskTotal($taskId);
        if ($totalLimit > 0 && $taskTotal > $totalLimit) {
            YCore::exception(STATUS_SERVER_ERROR, '该打卡已达签到次数上限');
        }
    }

    /**
     * 验证用户今日是否已打卡指定任务。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $taskId  任务 ID。
     *
     * @return void
     */
    private static function checkUserTodayIsCheckIn($userid, $taskId)
    {
        $userTodayIsDo = self::isTodayDo($userid, $taskId);
        if ($userTodayIsDo) {
            YCore::exception(STATUS_SERVER_ERROR, '您今日已经参与任务打卡了');
        }
    }

    /**
     * 验证用户指定打卡任务参与次数是否已达上限。
     *
     * @param  int  $userid      用户 ID。
     * @param  int  $taskId      任务 ID。
     * @param  int  $timesLimit  打卡任务每人参与上限。
     *
     * @return void
     */
    private static function checkUserCheckInTimes($userid, $taskId, $timesLimit)
    {
        if ($timesLimit > 0) {
            $taskTotal = self::getUserTaskTotal($userid, $taskId);
            if ($taskTotal >= $timesLimit) {
                YCore::exception(STATUS_SERVER_ERROR, '您打卡已超过上限');
            }
        }
    }

    /**
     * 获取用户指定打卡任务签到总次数。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $taskId  任务 ID。
     *
     * @return int
     */
    private static function getUserTaskTotal($userid, $taskId)
    {
        $cacheKey = "system_task_total:{$taskId}-{$userid}";
        $redis    = YCache::getRedisClient();
        $total    = $redis->get($cacheKey);
        if ($total !== FALSE) {
            return intval($total);
        } else {
            $lockKey = "system_task_today_lock:{$taskId}-{$userid}";
            $status  = RedisMutexLock::lock($lockKey, 3, 30);
            if ($status) {
                $TaskRecord = new TaskRecord();
                $total      = $TaskRecord->count(['taskid' => $taskId, 'userid' => $userid]);
                $redis->set($cacheKey, $total, ['EX' => 86400]);
                return intval($total);
            } else {
                $total = $redis->get($cacheKey);
                return intval($total);
            }
        }
    }

    /**
     * 用户今日是否已参与打卡。
     *
     * @param  int  $userid      用户 ID。
     * @param  int  $taskId      任务 ID。
     *
     * @return bool true-已参与、false-未参与。
     */
    private static function isTodayDo($userid, $taskId)
    {
        $timestamp = time(); 
        $yymmdd    = date('Ymd', $timestamp);
        $cacheKey  = "sys_task_checkin_status_{$yymmdd}:taskid_{$taskId}_userid_{$userid}";
        $redis     = YCache::getRedisClient();
        $status    = $redis->get($cacheKey);
        if ($status !== FALSE) {
            return $status==1 ? true : false;
        } else {
            $starTime = date('Y-m-d 00:00:00', $timestamp);
            $endTime  = date('Y-m-d 23:59:59', $timestamp);
            $where    = [
                'userid' => $userid, 
                'taskid' => $taskId, 
                'c_time' => ['BETWEEN', [$starTime, $endTime]]
            ];
            $TaskRecordModel = new TaskRecord();
            $taskRecord      = $TaskRecordModel->fetchOne(['id'], $where);
            $status          = empty($taskRecord) ? 0 : 1;
            $redis->set($cacheKey, $status, ['NX', 'EX' => 86400]);
            return empty($taskRecord) ? false : true;
        }
    }

    /**
     * 设置用户今天已经参与该打卡任务。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $taskId  任务 ID。
     *
     * @return void
     */
    private static function setTodayAlreadyDo($userid, $taskId)
    {
        $timestamp = time();
        $yymmdd    = date('Ymd', $timestamp);
        self::setTodayTaskCount($taskId);
        self::setTaskTotal($taskId);
        $cacheKey  = "sys_task_checkin_status_{$yymmdd}:taskid_{$taskId}_userid_{$userid}";
        $redis     = YCache::getRedisClient();
        $redis->set($cacheKey, 1, ['EX' => 86400]);
    }

    /**
     * 设置打卡任务今日参与次数。
     * 
     * -- 注1：当缓存不存在的时候，由读取的时候再进行重置即可。
     * -- 注2：如果在此处进行重算缓存，会导致并发计数不准确的情况发生。
     * 
     * @param  int  $taskId  任务 ID。
     *
     * @return void
     */
    private static function setTodayTaskCount($taskId)
    {
        $yymmdd   = date('Ymd', time());
        $cacheKey = "system_task_today_{$yymmdd}:{$taskId}";
        $redis    = YCache::getRedisClient();
        $total    = $redis->get($cacheKey);
        if ($total !== FALSE) {
            $redis->incr($cacheKey, 1);
        }
    }

    /**
     * 获取打卡任务今日参与次数。
     *
     * @param  int  $taskId  任务 ID。
     *
     * @return int
     */
    private static function getTodayTaskCount($taskId)
    {
        $timestamp = time();
        $yymmdd    = date('Ymd', $timestamp);
        $cacheKey  = "system_task_today_{$yymmdd}:{$taskId}";
        $redis     = YCache::getRedisClient();
        $total     = $redis->get($cacheKey);
        if ($total !== FALSE) {
            return intval($total);
        } else {
            $lockKey = "system_task_today_lock_{$yymmdd}:{$taskId}";
            $status  = RedisMutexLock::lock($lockKey, 3, 30);
            if ($status) {
                $startTime  = date('Y-m-d 00:00:00', $timestamp);
                $endTime    = date('Y-m-d 23:59:59', $timestamp);
                $where      = [
                    'taskid' => $taskId,
                    'c_time' => ['BETWEEN', [$startTime, $endTime]]
                ];
                $TaskRecord = new TaskRecord();
                $total      = $TaskRecord->count($where);
                $redis->set($cacheKey, $total, ['NX', 'EX' => 86400]);
                return $total;
            } else {
                $total = $redis->get($cacheKey);
                return intval($total);
            }
        }
    }

    /**
     * 设置任务参与总次数。
     * 
     * -- 注1：当缓存不存在的时候，由读取的时候再进行重置即可。
     * -- 注2：如果在此处进行重算缓存，会导致并发计数不准确的情况发生。
     * 
     * @param  int  $taskId  任务 ID。
     *
     * @return void
     */
    private static function setTaskTotal($taskId)
    {
        $cacheKey = "system_task_total:{$taskId}";
        $redis    = YCache::getRedisClient();
        $total    = $redis->get($cacheKey);
        if ($total !== FALSE) {
            $redis->incr($cacheKey, 1);
        }
    }

    /**
     * 获取打卡任务总参数次数。
     *
     * @param  int  $taskId  任务 ID。
     * 
     * @return int
     */
    private static function getTaskTotal($taskId)
    {
        $cacheKey = "system_task_total:{$taskId}";
        $redis    = YCache::getRedisClient();
        $total    = $redis->get($cacheKey);
        if ($total !== FALSE) {
            return intval($total);
        } else {
            $lockKey = "system_task_total_lock:{$taskId}";
            $status  = RedisMutexLock::lock($lockKey, 3, 30);
            if ($status) {
                $TaskRecord = new TaskRecord();
                $total      = $TaskRecord->count(['taskid' => $taskId]);
                $redis->set($cacheKey, $total);
                return intval($total);
            } else {
                $total = $redis->get($cacheKey);
                return intval($total);
            }
        }
    }

    /**
     * 增加金币。
     *
     * @param  int  $userid  用户 ID。
     * @param  int  $gold    金币。
     *
     * @return void
     */
    private static function addGold($userid, $gold)
    {
        try {
            Gold::consume($userid, $gold, 1, 'task');
        } catch (\Exception $e) {
            Db::rollBack();
            YCore::exception($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 距离计算。
     *
     * @param  float  $addrLong   打卡地点经度。
     * @param  float  $addrLat    打卡地点纬度。
     * @param  float  $longitude  用户所在位置经度。
     * @param  float  $latitude   用户所在位置纬度。
     *
     * @return int
     */
    private static function distance($addrLong, $addrLat, $longitude, $latitude)
    {
        return YCore::distance($addrLong, $addrLat, $longitude, $latitude);
    }
}