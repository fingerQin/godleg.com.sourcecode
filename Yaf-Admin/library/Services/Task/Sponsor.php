<?php
/**
 * 打卡任务主办方业务封装。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Services\Task;

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
        $where = ' WHERE a.status = :status'; 
        if (strlen($name) > 0) {
            $where .= ' AND a.name = :name ';
            $params[':name'] = $name;
        }
        $sql   = "SELECT COUNT(1) AS count FROM finger_task_sponsor AS a {$where} ";
        $total = Db::count($sql, $params);
        $sql   = "SELECT a.sponsorid, a.name, a.address, a.longitude, a.latitude, a.c_time, a.u_time, "
               . "a.link_man, a.link_phone, concat(b.province_name,b.city_name,b.district_name) AS district "
               . "FROM finger_task_sponsor AS a LEFT JOIN finger_district AS b "
               . "ON(a.district_code=b.district_code AND b.region_type = 3) "
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
     * @param  string  $linkMan         主办方联系人。
     * @param  string  $linkPhone       主国方联系方式。
     * @param  int     $adminId         操作管理员 ID。
     * 
     * @return array
     */
    public static function add($name, $address, $districtCode, $albums, $longitude, $latitude, $linkMan, $linkPhone, $adminId)
    {
        $data = [
            'name'          => $name,
            'address'       => $address,
            'district_code' => $districtCode,
            'longitude'     => $longitude,
            'latitude'      => $latitude,
            'link_man'      => $linkMan,
            'link_phone'    => $linkPhone
        ];
        $rules = [
            'name'          => '主办方名称|require|len:1:100:1',
            'address'       => '主办方地址|require|len:1:200:1',
            'district_code' => '地址编码|require|integer',
            'longitude'     => '纬度|require|float',
            'latitude'      => '纬度|require|float',
            'link_man'      => '联系人|require|len:1:50:1',
            'link_phone'    => '联系方式|require'
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
        self::checkLinkPhone($linkPhone);
        $albums = self::filterAlbums($albums);
        $data['longitude'] = bcadd($longitude, 0, 6);
        $data['latitude']  = bcadd($latitude, 0, 6);
        $data['albums']    = json_encode($albums);
        $data['c_time']    = date('Y-m-d H:i:s', time());
        $data['c_by']      = $adminId;
        $data['status']    = TaskSponsor::STATUS_YES;
        $sponsorModel = new TaskSponsor();
        $ok = $sponsorModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '添加失败');
        }
    }

    /**
     * 编辑主办方。
     *
     * @param  int     $sponsorId       主办主 ID。
     * @param  string  $name            主办方名称。
     * @param  string  $address         主办方所在地。
     * @param  string  $districtCode    主办方所在区/县。
     * @param  array   $albums          主办方相册图片。
     * @param  float   $longitude       主办方所在地经度。
     * @param  float   $latitude        主办方所在地纬度。
     * @param  string  $linkMan         主办方联系人。
     * @param  string  $linkPhone       主国方联系方式。
     * @param  int     $adminId         操作管理员 ID。
     *
     * @return void
     */
    public static function edit($sponsorId, $name, $address, $districtCode, $albums, $longitude, $latitude, $linkMan, $linkPhone, $adminId)
    {
        self::detail($sponsorId);
        $data = [
            'name'          => $name,
            'address'       => $address,
            'district_code' => $districtCode,
            'longitude'     => $longitude,
            'latitude'      => $latitude,
            'link_man'      => $linkMan,
            'link_phone'    => $linkPhone
        ];
        $rules = [
            'name'          => '主办方名称|require|len:1:100:1',
            'address'       => '主办方地址|require|len:1:200:1',
            'district_code' => '地址编码|require|integer',
            'longitude'     => '纬度|require|float',
            'latitude'      => '纬度|require|float',
            'link_man'      => '联系人|require|len:1:50:1',
            'link_phone'    => '联系方式|require'
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
        self::checkLinkPhone($linkPhone);
        $albums = self::filterAlbums($albums);
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
     * 验证联系方式。
     *
     * @param  string  $linkPhone  联系方式。
     *
     * @return void
     */
    private static function checkLinkPhone($linkPhone)
    {
        $isMobile   = Validator::is_mobilephone($linkPhone);
        $isTelPhone = Validator::is_telephone($linkPhone);
        if (!$isMobile && !$isTelPhone) {
            YCore::exception(STATUS_SERVER_ERROR, '联系方式不正确');
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
     * 主办方详情。
     *
     * @param  int  $sponsorId  主办方 ID。
     *
     * @return array
     */
    public static function detail($sponsorId)
    {
        $columns = 'sponsorid, name, district_code, address, albums, longitude, latitude, link_man, link_phone';
        $detail  = (new TaskSponsor())->fetchOne($columns, ['sponsorid' => $sponsorId, 'status' => TaskSponsor::STATUS_YES]);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        $detail['albums'] = json_decode($detail['albums'], true);
        $detail['albums'] = self::resetAlbums($detail['albums']);
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
            'u_time' => date('Y-m-d H:i:s', time()),
            'status' => TaskSponsor::STATUS_DELETED
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