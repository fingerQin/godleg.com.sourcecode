<?php
/**
 * 打卡记录业务封装。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Services\System;

class Record extends \Services\AbstractBase
{
    /**
     * 打卡记录列表。
     *
     * @param  int     $userid     用户 ID。
     * @param  int     $taskId     打卡任务 ID。
     * @param  int     $sponsorId  主办方 ID。
     * @param  string  $startTime  查询开始时间。
     * @param  string  $endTime    查询结束时间。
     * @param  int     $page       页码。
     * @param  int     $count      每页条数。
     *
     * @return array
     */
    public static function lists($userid = -1, $taskId = -1, $sponsorId = -1, $startTime = '', $endTime = '', $page = 1, $count = 20)
    {
        $offset = self::getPaginationOffset($page, $count);
        $params = [];
        $where  = ' WHERE 1 ';
        if ($userid != -1) {
            $where .= ' AND a.userid = :userid ';
            $params[':userid'] = $userid;
        }
        if ($taskId != -1) {
            $where .= ' AND a.taskid = :taskid ';
            $params[':taskid'] = $taskId;
        }
        if ($sponsorId != -1) {
            $where .= ' AND a.sponsorid = :sponsorid ';
            $params[':sponsorid'] = $sponsorId;
        }
        if (strlen($startTime) > 0) {
            $where .= ' AND a.c_time = :startTime ';
            $params[':startTime'] = $startTime;
        }
        if (strlen($endTime) > 0) {
            $where .= ' AND a.c_time = :endTime ';
            $params[':endTime'] = $endTime;
        }
        $sql   = "SELECT COUNT(1) AS count FROM finger_task_record AS a INNER JOIN finger_task AS b "
               . "ON(a.taskid=b.taskid) WHERE {$where} ";
        $total = Db::count($sql, $params);
        $sql   = "SELECT a.id, a.gold, a.step_count, a.image_url, a.sponsorid, a.c_time, b.taskid, b.task_name "
               . "FROM finger_task_record AS a INNER JOIN finger_task AS b ON(a.taskid=b.taskid) "
               . "{$where} ORDER BY a.id DESC LIMIT {$offset},{$count}";
        $list = Db::all($sql, $params);
        $result      = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }
}