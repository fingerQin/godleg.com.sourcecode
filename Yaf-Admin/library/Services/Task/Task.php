<?php
/**
 * 打卡任务业务封装。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Services\Task;

use Utils\YCore;
use finger\Validator;
use finger\Database\Db;
use Models\TaskSponsor;
use Models\Task as TaskModel;

class Task extends \Services\AbstractBase
{
    /**
     * 打卡任务列表。
     *
     * @param  int     $sponsorId  主办方 ID。
     * @param  string  $taskName   任务名称。
     * @param  int     $page       页码。
     * @param  int     $count      每页条数。
     *
     * @return array
     */
    public static function lists($sponsorId = -1, $taskName = '', $page = 1, $count = 20)
    {
        $offset = self::getPaginationOffset($page, $count);
        $params = [
            ':status' => TaskModel::STATUS_YES
        ];
        $where = ' WHERE a.status = :status ';
        if ($sponsorId != -1) {
            $where .= ' AND a.sponsorid = :sponsorid ';
            $params[':sponsorid'] = $sponsorId;
        }
        if (strlen($taskName) > 0) {
            $where .= ' AND a.task_name = :task_name ';
            $params[':task_name'] = $taskName;
        }
        $sql   = "SELECT COUNT(1) AS count FROM finger_task AS a LEFT JOIN finger_task_sponsor '
               . 'AS b ON (a.sponsorid=b.sponsorid) WHERE {$where} ";
        $total = Db::count($sql, $params);
        $sql   = "SELECT a.taskid, a.task_name, a.address, a.gold, a.move_step, a.times_limit, "
               . "a.longitude, a.latitude, a.display, b.name FROM finger_task AS a LEFT JOIN  "
               . "finger_task_sponsor AS b ON(a.sponsorid=b.sponsorid) "
               . "{$where} ORDER BY a.taskid DESC LIMIT {$offset},{$count}";
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

    /**
     * 添加打卡任务。
     * 
     * @param  array  $data     任务数据。
     * @param  array  $adminId  操作管理员 ID。
     * 
     * -- eg:start --
     * $data = [
     *  'sponsorId'  => 'int:主办方 ID',
     *  'taskName'   => 'string:任务名称',
     *  'address'    => 'string:打卡地址',
     *  'gold'       => 'int:打卡金币数量',
     *  'moveStep'   => 'int:运动步数',
     *  'timesLimit' => 'int:次数限制',
     *  'longitude'  => 'float:经度',
     *  'latitude'   => 'float:纬度',
     *  'albums'     => 'array:活动宣传相册图片',
     *  'display'    => 'int:是否显示',
     * ];
     * -- eg:end --
     *
     * @return void
     */
    public static function add($data, $adminId)
    {
        $rules = [
            'sponsorId'  => '主办方ID|require|int',
            'taskName'   => '打卡任务名称|require|len:1:30',
            'address'    => '打卡地址|require|len:1:200',
            'gold'       => '金币数量|require|int|number_between:1:1000000',
            'moveStep'   => '运动步数|require|int|number_between:1:100000',
            'timesLimit' => '次数限制|require|int|number_between:0:1000',
            'longitude'  => '经度|require|float',
            'latitude'   => '纬度|require|float',
            'display'    => '是否显示|require|int|number_between:0:1'
        ];
        Validator::valido($data, $rules);
        $sponsor = (new TaskSponsor())->fetchOne(['sponsorid'], 
            [
                'sponsorid' => $data['sponsorid'], 
                'status' => TaskSponsor::STATUS_YES
            ]
        );
        if (empty($sponsor)) {
            YCore::exception(STATUS_SERVER_ERROR, '主办方不存在或已经删除');
        }
        if ($data['longitude'] > 180 || $data['longitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '经度值不正确');
        }
        if ($data['latitude'] > 90 || $data['latitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '纬度值不正确');
        }
        $data['albums'] = json_encode($data['albums']);
        $data['c_by']   = $adminId;
        $data['c_time'] = date('Y-m-d H:i:s', time());
        $TaskModel      = new TaskModel();
        $ok = $TaskModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '添加失败');
        }
    }

    /**
     * 编辑打卡任务。
     *
     * @param  array  $data     任务数据。
     * @param  array  $adminId  操作管理员 ID。
     * 
     * -- eg:start --
     * $data = [
     *  'taskId'     => 'int:打卡任务 ID',
     *  'sponsorId'  => 'int:主办方 ID',
     *  'taskName'   => 'string:任务名称',
     *  'address'    => 'string:打卡地址',
     *  'gold'       => 'int:打卡金币数量',
     *  'moveStep'   => 'int:运动步数',
     *  'timesLimit' => 'int:次数限制',
     *  'longitude'  => 'float:经度',
     *  'latitude'   => 'float:纬度',
     *  'albums'     => 'array:活动宣传相册图片',
     *  'display'    => 'int:是否显示'
     * ];
     * -- eg:end --
     * @return void
     */
    public static function edit($data, $adminId)
    {
        $rules = [
            'taskId'     => '打卡任务ID|require|int',
            'sponsorId'  => '主办方ID|require|int',
            'taskName'   => '打卡任务名称|require|len:1:30',
            'address'    => '打卡地址|require|len:1:200',
            'gold'       => '金币数量|require|int|number_between:1:1000000',
            'moveStep'   => '运动步数|require|int|number_between:1:100000',
            'timesLimit' => '次数限制|require|int|number_between:0:1000',
            'longitude'  => '经度|require|float',
            'latitude'   => '纬度|require|float',
            'display'    => '是否显示|require|int|number_between:0:1'
        ];
        Validator::valido($data, $rules);
        self::detail($data['taskId']);
        $sponsor = (new TaskSponsor())->fetchOne(['sponsorid'], 
            [
                'sponsorid' => $data['sponsorid'], 
                'status' => TaskSponsor::STATUS_YES
            ]
        );
        if (empty($sponsor)) {
            YCore::exception(STATUS_SERVER_ERROR, '主办方不存在或已经删除');
        }
        if ($data['longitude'] > 180 || $data['longitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '经度值不正确');
        }
        if ($data['latitude'] > 90 || $data['latitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '纬度值不正确');
        }
        $data['albums'] = json_encode($data['albums']);
        $data['u_by']   = $adminId;
        $data['u_time'] = date('Y-m-d H:i:s', time());
        $TaskModel      = new TaskModel();
        $ok = $TaskModel->update($data, ['taskid' => $data['taskId'], 'status' => TaskModel::STATUS_YES]);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '添加失败');
        }
    }

    /**
     * 打卡任务删除。
     *
     * @param  int  $taskId   任务 ID。
     * @param  int  $adminId  操作管理员 ID。
     *
     * @return void
     */
    public static function delete($taskId, $adminId)
    {
        $updata = [
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time()),
            'status' => TaskModel::STATUS_DELETED
        ];
        $where = [
            'taskid' => $taskId,
            'status' => TaskModel::STATUS_YES
        ];
        $TaskModel = new TaskModel();
        $ok = $TaskModel->update($updata, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '删除失败,请稍候刷新重试');
        }
    }

    /**
     * 任务详情。
     *
     * @param  int  $taskId  任务 ID。
     *
     * @return array
     */
    public static function detail($taskId)
    {
        $columns = 'taskid,sponsorid,task_name,address,district_code,gold,'
                 . 'move_step,times_limit,albums,longitude,latitude,display';
        $TaskModel = new TaskModel();
        $taskInfo  = $TaskModel->fetchOne([$columns, ['taskid' => $taskId], 'status' => TaskModel::STATUS_YES]);
        if (empty($taskInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '打卡任务不存在或已经删除');
        }
        $taskInfo['albums'] = json_decode($taskInfo['albums'], true);
        return $taskInfo;
    }
}