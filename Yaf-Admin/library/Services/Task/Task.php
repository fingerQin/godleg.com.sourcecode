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
use Models\District;
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
        $sql   = "SELECT COUNT(1) AS count FROM finger_task AS a LEFT JOIN finger_task_sponsor "
               . "AS b ON (a.sponsorid=b.sponsorid) {$where} ";
        $total = Db::count($sql, $params);
        $sql   = "SELECT a.taskid, a.task_name, a.address, a.gold, a.move_step, a.times_limit, "
               . "a.longitude, a.latitude, a.display, b.name, a.c_time, a.u_time, "
               . "concat(c.province_name,c.city_name,c.district_name) AS district "
               . "FROM finger_task AS a LEFT JOIN  "
               . "finger_task_sponsor AS b ON(a.sponsorid=b.sponsorid) "
               . "LEFT JOIN finger_district AS c ON(a.district_code=c.district_code AND c.region_type=3) "
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
     * ```
     * -- eg:start --
     * $data = [
     *  'sponsorid'      => 'int:主办方 ID',
     *  'task_name'      => 'string:任务名称',
     *  'address'        => 'string:打卡地址',
     *  'gold'           => 'int:打卡金币数量',
     *  'move_step'      => 'int:运动步数',
     *  'times_limit'    => 'int:次数限制',
     *  'longitude'      => 'float:经度',
     *  'latitude'       => 'float:纬度',
     *  'albums'         => 'array:活动宣传相册图片',
     *  'display'        => 'int:是否显示',
     *  'start_time'     => 'string:开始时间',
     *  'end_time'       => 'string:结束时间',
     *  'everyday_times' => 'int:每日参与上限',
     *  'total_times'    => 'int:总参与上限',
     *  'district_code'  => 'int:区县编码'
     * ];
     * -- eg:end --
     * ```
     *
     * @return void
     */
    public static function add($data, $adminId)
    {
        $rules = [
            'sponsorid'      => '主办方ID|require|integer',
            'task_name'      => '打卡任务名称|require|len:1:30:1',
            'address'        => '打卡地址|require|len:1:200:1',
            'gold'           => '金币数量|require|integer|number_between:1:1000000',
            'move_step'      => '运动步数|require|integer|number_between:1:100000',
            'times_limit'    => '次数限制|require|integer|number_between:0:1000',
            'longitude'      => '经度|require|float',
            'latitude'       => '纬度|require|float',
            'display'        => '是否显示|require|integer|number_between:0:1',
            'start_time'     => '开始时间|require|datetime',
            'end_time'       => '结束时间|require|datetime',
            'everyday_times' => '每日参与上限|require|integer|number_between:0:100000',
            'total_times'    => '总参与上限|require|integer|number_between:0:100000',
            'district_code'  => '区县编码|require|integer'
        ];
        Validator::valido($data, $rules);
        $sponsor = (new TaskSponsor())->fetchOne(['sponsorid'], 
            [
                'sponsorid' => $data['sponsorid'], 
                'status'    => TaskSponsor::STATUS_YES
            ]
        );
        if (empty($sponsor)) {
            YCore::exception(STATUS_SERVER_ERROR, '主办方不存在或已经删除');
        }
        $districtModel = new District();
        $districtInfo  = $districtModel->fetchOne([], 
            [
                'district_code' => $data['district_code'],
                'status'        => District::STATUS_YES
            ]
        );
        if (empty($districtInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '地区编码不正确');
        }
        if ($data['longitude'] > 180 || $data['longitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '经度值不正确');
        }
        if ($data['latitude'] > 90 || $data['latitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '纬度值不正确');
        }
        $data['albums'] = self::filterAlbums($data['albums']);
        $data['albums'] = json_encode($data['albums']);
        $data['c_by']   = $adminId;
        $data['c_time'] = date('Y-m-d H:i:s', time());
        $data['status'] = TaskModel::STATUS_YES;
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
     * ```
     * -- eg:start --
     * $data = [
     *  'taskid'         => 'int:打卡任务 ID',
     *  'task_name'      => 'string:任务名称',
     *  'address'        => 'string:打卡地址',
     *  'gold'           => 'int:打卡金币数量',
     *  'move_step'      => 'int:运动步数',
     *  'times_limit'    => 'int:次数限制',
     *  'longitude'      => 'float:经度',
     *  'latitude'       => 'float:纬度',
     *  'albums'         => 'array:活动宣传相册图片',
     *  'display'        => 'int:是否显示',
     *  'start_time'     => 'string:开始时间',
     *  'end_time'       => 'string:结束时间',
     *  'everyday_times' => 'int:每日参与上限',
     *  'total_times'    => 'int:总参与上限',
     *  'district_code'  => 'int:区县编码'
     * ];
     * -- eg:end --
     * ```
     * 
     * @return void
     */
    public static function edit($data, $adminId)
    {
        $rules = [
            'taskid'         => '打卡任务ID|require|integer',
            'task_name'      => '打卡任务名称|require|len:1:30:1',
            'address'        => '打卡地址|require|len:1:200:1',
            'gold'           => '金币数量|require|integer|number_between:1:1000000',
            'move_step'      => '运动步数|require|integer|number_between:1:100000',
            'times_limit'    => '次数限制|require|integer|number_between:0:1000',
            'longitude'      => '经度|require|float',
            'latitude'       => '纬度|require|float',
            'display'        => '是否显示|require|integer|number_between:0:1',
            'start_time'     => '开始时间|require|datetime',
            'end_time'       => '结束时间|require|datetime',
            'everyday_times' => '每日参与上限|require|integer|number_between:0:100000',
            'total_times'    => '总参与上限|require|integer|number_between:0:100000',
            'district_code'  => '区县编码|require|integer'
        ];
        Validator::valido($data, $rules);
        self::detail($data['taskid']);
        $districtModel = new District();
        $districtInfo  = $districtModel->fetchOne([], 
            [
                'district_code' => $data['district_code'], 
                'status'        => District::STATUS_YES
            ]
        );
        if (empty($districtInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '地区编码不正确');
        }
        if ($data['longitude'] > 180 || $data['longitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '经度值不正确');
        }
        if ($data['latitude'] > 90 || $data['latitude'] < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '纬度值不正确');
        }
        $data['albums'] = self::filterAlbums($data['albums']);
        $data['albums'] = json_encode($data['albums']);
        $data['u_by']   = $adminId;
        $data['u_time'] = date('Y-m-d H:i:s', time());
        $TaskModel      = new TaskModel();
        $ok = $TaskModel->update($data, ['taskid' => $data['taskid'], 'status' => TaskModel::STATUS_YES]);
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
     * 对相册进行格式化处理。
     *
     * @param  array  $albums  相册数据。
     *
     * @return array
     */
    private static function filterAlbums(&$albums)
    {
        $_albums = [];
        foreach ($albums as $album) {
            if (strlen($album) != 0) {
                $_albums[] = $album;
            }
        }
        return $_albums;
    }

    /**
     * 还原相册添加时的样子。
     *
     * @param  array  $albums  相册。
     * 
     * @return array
     */
    private static function resetAlbums($albums)
    {
        $albums[0] = $albums[0] ?? '';
        $albums[1] = $albums[1] ?? '';
        $albums[2] = $albums[2] ?? '';
        $albums[3] = $albums[3] ?? '';
        $albums[4] = $albums[4] ?? '';
        return $albums;
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
                 . 'move_step,times_limit,albums,longitude,latitude,display,'
                 . 'start_time,end_time,everyday_times,total_times';
        $TaskModel = new TaskModel();
        $taskInfo  = $TaskModel->fetchOne($columns, ['taskid' => $taskId, 'status' => TaskModel::STATUS_YES]);
        if (empty($taskInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '打卡任务不存在或已经删除');
        }
        $taskInfo['albums'] = json_decode($taskInfo['albums'], true);
        $taskInfo['albums'] = self::resetAlbums($taskInfo['albums']);
        return $taskInfo;
    }
}