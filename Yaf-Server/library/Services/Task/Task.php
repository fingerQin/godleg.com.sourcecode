<?php
/**
 * 打卡任务业务封装。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Services\Task;

use finger\Database\Db;
use Models\TaskSponsor;
use Models\Task as TaskModel;
use Services\Gold\Gold;
use Utils\YCore;

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
            'isnext' => self::IsHasNextPage($total, $page, $count)
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
        $columns   = ' a.taskid, a.task_name, a.sponsorid, a.address, a.gold, a.move_step, a.albums, a.name ';
        $where     = ' WHERE a.status = :status AND a.district_code = :district_code ';
        $params    = [
            ':status'        => TaskSponsor::STATUS_YES,
            ':district_code' => $districtCode
        ];
        $orderBy = "ORDER BY a.taskid DESC";
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        $result  = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
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
        $TaskModel = new TaskModel();
        $task = $TaskModel->fetchOne([], $where);
        if (empty($task)) {
            YCore::exception(STATUS_SERVER_ERROR, '打卡任务不存在或已经删除');
        }
        if ($stepCount < $task['move_step']) {
            YCore::exception(STATUS_SERVER_ERROR, "今日运动步数不足{$task['move_step']}步");
        }
        $distance = self::distance($task['longitude'], $task['latitude'], $longitude, $latitude);
        if ($distance > 50) {
            YCore::exception(STATUS_SERVER_ERROR, '不在打卡地点范围内');
        }
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
        $ok = $TaskModel->insert($data);
        if (!$ok) {
            Db::rollBack();
            YCore::exception(STATUS_SERVER_ERROR, '增加失败');
        }
        self::addGold($userid, $task['gold']);
        Db::commit();
    }

    /**
     * 用户今日是否已参与打卡。
     *
     * @param  int  $userid      用户 ID。
     * @param  int  $taskid      任务 ID。
     * @param  int  $timesLimit  参与次数限制。
     *
     * @return bool
     */
    private static function isTodayDo($userid, $taskid, $timesLimit)
    {

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