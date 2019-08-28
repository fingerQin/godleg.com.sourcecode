<?php
/**
 * 打卡任务主办方业务封装。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Services\System;

use Utils\YCore;
use finger\Validator;
use finger\Database\Db;
use Models\District;
use Models\TaskSponsor;

class Sponsor extends \Services\AbstractBase
{
    /**
     * 主办方列表。
     *
     * @param  int  $page   页码。
     * @param  int  $count  每页条数。
     *
     * @return array
     */
    public static function lists($name = '', $page = 1, $count = 20)
    {
        $offset = self::getPaginationOffset($page, $count);
        $params = [
            ':status' => TaskSponsor::STATUS_YES
        ];
        $where = ' WHERE status = :status';
        if (strlen($name) > 0) {
            $where .= ' AND name = :name ';
            $params[':name'] = $name;
        }
        $sql   = "SELECT COUNT(1) AS count FROM finger_task_sponsor WHERE {$where} ";
        $total = Db::count($sql, $params);
        $sql   = "SELECT sponsorid, name, address, longitude, latitude FROM finger_task_sponsor "
               . "{$where} ORDER BY sponsorid DESC LIMIT {$offset},{$count}";
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
     * 主办方添加。
     *
     * @param  string  $name            主办方名称。
     * @param  string  $address         主办方所在地。
     * @param  string  $districtCode    主办方所在区/县。
     * @param  array   $albums          主办方相册图片。
     * @param  float   $longitude       主办方所在地经度。
     * @param  float   $latitude        主办方所在地纬度。
     * @param  int     $adminId         操作管理员 ID。
     * 
     * @return array
     */
    public static function add($name, $address, $districtCode, $albums, $longitude, $latitude, $adminId)
    {
        $data = [
            'name'         => $name,
            'address'      => $address,
            'districtCode' => $districtCode,
            'longitude'    => $longitude,
            'latitude'     => $latitude
        ];
        $rules = [
            'name'         => '主办方名称|require|len:1:100:1',
            'address'      => '主办方地址|require|len:1:200:1',
            'districtCode' => '地址编码|require|int',
            'longitude'    => '纬度|require|float',
            'latitude'     => '纬度|require|float'
        ];
        Validator::valido($data, $rules);
        $districtModel = new District();
        $districtInfo  = $districtModel->fetchOne([], ['district_code' => $districtCode, 'status' => District::STATUS_YES]);
        if (empty($districtInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '地区编码不正确');
        }
        if ($longitude > 180 || $longitude < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '经度值不正确');
        }
        if ($latitude > 90 || $latitude < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '纬度值不正确');
        }
        $data['longitude'] = bcadd($longitude, 0, 6);
        $data['latitude']  = bcadd($latitude, 0, 6);
        $data['albums']    = json_encode($albums);
        $data['c_time']    = date('Y-m-d H:i:s', time());
        $data['c_by']      = $adminId;
        $sponsorModel = new TaskSponsor();
        $ok = $sponsorModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '添加失败');
        }
    }

    /**
     * 编辑主办方。
     *
     * @param [type] $sponsorId
     * @param  string  $name            主办方名称。
     * @param  string  $address         主办方所在地。
     * @param  string  $districtCode    主办方所在区/县。
     * @param  array   $albums          主办方相册图片。
     * @param  float   $longitude       主办方所在地经度。
     * @param  float   $latitude        主办方所在地纬度。
     * @param  int     $adminId         操作管理员 ID。
     *
     * @return void
     */
    public static function edit($sponsorId, $name, $address, $districtCode, $albums, $longitude, $latitude, $adminId)
    {
        self::detail($sponsorId);
        $data = [
            'name'         => $name,
            'address'      => $address,
            'districtCode' => $districtCode,
            'longitude'    => $longitude,
            'latitude'     => $latitude
        ];
        $rules = [
            'name'         => '主办方名称|require|len:1:100:1',
            'address'      => '主办方地址|require|len:1:200:1',
            'districtCode' => '地址编码|require|int',
            'longitude'    => '纬度|require|float',
            'latitude'     => '纬度|require|float'
        ];
        Validator::valido($data, $rules);
        $districtModel = new District();
        $districtInfo  = $districtModel->fetchOne([], ['district_code' => $districtCode, 'status' => District::STATUS_YES]);
        if (empty($districtInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '地区编码不正确');
        }
        if ($longitude > 180 || $longitude < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '经度值不正确');
        }
        if ($latitude > 90 || $latitude < 0) {
            YCore::exception(STATUS_SERVER_ERROR, '纬度值不正确');
        }
        $data['longitude'] = bcadd($longitude, 0, 6);
        $data['latitude']  = bcadd($latitude, 0, 6);
        $data['u_time']    = date('Y-m-d H:i:s', time());
        $data['albums']    = json_encode($albums);
        $data['u_by']      = $adminId;
        $sponsorModel = new TaskSponsor();
        $ok = $sponsorModel->update($data, ['sponsorid' => $sponsorId, 'status' => TaskSponsor::STATUS_YES]);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }

    /**
     * 主办方详情。
     *
     * @param  int  $sponsorId  主办方 ID。
     *
     * @return array
     */
    public static function detail($sponsorId)
    {
        $columns = 'sponsorid, name, district_code, address, albums, longitude, latitude';
        $detail  = (new TaskSponsor())->fetchOne($columns, ['sponsorid' => $sponsorId, 'status' => TaskSponsor::STATUS_YES]);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        $detail['albums'] = json_decode($detail['albums'], true);
        return $detail;
    }

    /**
     * 删除主办方。
     *
     * @param  int  $sponsorId  主办方 ID。
     * @param  int  $adminId    操作管理员 ID。
     *
     * @return void
     */
    public static function delete($sponsorId, $adminId)
    {
        $updata = [
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'sponsorid' => $sponsorId,
            'status'    => TaskSponsor::STATUS_YES
        ];
        $TaskSponsorModel = new TaskSponsor();
        $ok = $TaskSponsorModel->update($updata, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '更新失败');
        }
    }
}