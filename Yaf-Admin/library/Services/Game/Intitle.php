<?php
/**
 * 取名库管理。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Core;
use finger\Validator;
use finger\Database\Db;
use Models\GmNameDict;
use ApiTools\Request;

class Intitle extends \Services\AbstractBase
{
    /**
     * 名字类型字典。
     *
     * @var array
     */
    public static $typeDict = [
        GmNameDict::TYPE_SINGLE => '单字',
        GmNameDict::TYPE_DOUBLE => '双字'
    ];

    /**
     * 名字性别字典。
     *
     * @var array
     */
    public static $sexDict = [
        GmNameDict::SEX_MALE   => '男孩',
        GmNameDict::SEX_FEMALE => '女孩'
    ];

    /**
     * 列表。
     *
     * @param  string  $name   名字。
     * @param  int     $type   类型。1-单字、2-双字。
     * @param  string  $sex    性别：fmale-女、male-男。
     * @param  int     $page   页码。
     * @param  int     $count  每页显示条数。
     *
     * @return array
     */
    public static function list($name = '', $type = -1, $sex = '', $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_name_dict ';
        $columns   = ' id, name, type, sex, expl, c_time, u_time ';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmNameDict::STATUS_YES
        ];
        if (strlen($name) > 0) {
            $where .= ' AND name = :name ';
            $params[':name'] = $name;
        }
        if ($type != -1) {
            $where .= ' AND type = :type ';
            $params[':type'] = $type;
        }
        if (strlen($sex) > 0) {
            $where .= ' AND sex = :sex ';
            $params[':sex'] = $sex;
        }
        $orderBy = ' ORDER BY id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['type'] = self::$typeDict[$item['type']];
            $item['sex']  = self::$sexDict[$item['sex']];
            $list[$key]   = $item;
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 详情。
     *
     * @param  int  $id  记录 ID。
     *
     * @return array
     */
    public static function detail($id)
    {
        $NameDictModel = new GmNameDict();
        $columns = ['id', 'name', 'type', 'sex', 'expl'];
        $where = [
            'id'     => $id,
            'status' => GmNameDict::STATUS_YES
        ];
        $detail = $NameDictModel->fetchOne($columns, $where);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        return $detail;
    }

    /**
     * 添加。
     *
     * @param  int     $adminId  管理员 ID。
     * @param  string  $name     名字。
     * @param  int     $type     类型。1-单字、2-双字。
     * @param  int     $sex      性别：fmale-女、male-男。
     * @param  string  $expl     名字解释。
     *
     * @return array
     */
    public static function add($adminId, $name, $type, $sex, $expl)
    {
        $rules = [
            'name' => '名字|require|len:1:2:1|chinese',
            'type' => '类型|require|integer|number_between:1:2',
            'sex'  => '性别|require',
            'expl' => '名字解释|require|len:0:100:1'
        ];
        $data = [
            'name' => $name,
            'type' => $type,
            'sex'  => $sex,
            'expl' => $expl
        ];
        Validator::valido($data, $rules);
        if (!array_key_exists($sex, self::$sexDict)) {
            Core::exception(STATUS_SERVER_ERROR, '性别参数有误!');
        }
        $datetme        = date('Y-m-d H:i:s', time());
        $data['c_by']   = $adminId;
        $data['c_time'] = $datetme;
        $data['u_time'] = $datetme;
        $NameDictModel  = new GmNameDict();
        $ok = $NameDictModel->insert($data);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试!');
        }
    }

    /**
     * 编辑。
     *
     * @param  int     $adminId  管理员 ID。
     * @param  int     $id       记录 ID。
     * @param  string  $name     名字。
     * @param  int     $type     类型。1-单字、2-双字。
     * @param  int     $sex      性别：fmale-女、male-男。
     * @param  string  $expl     名字解释。
     *
     * @return array
     */
    public static function edit($adminId, $id, $name, $type, $sex, $expl)
    {
        $rules = [
            'name' => '名字|require|len:1:2:1|chinese',
            'type' => '类型|require|integer|number_between:1:2',
            'sex'  => '性别|require',
            'expl' => '名字解释|require|len:0:100:1'
        ];
        $data = [
            'name' => $name,
            'type' => $type,
            'sex'  => $sex,
            'expl' => $expl
        ];
        if (!array_key_exists($sex, self::$sexDict)) {
            Core::exception(STATUS_SERVER_ERROR, '性别参数有误!');
        }
        $datetme        = date('Y-m-d H:i:s', time());
        $data['u_by']   = $adminId;
        $data['u_time'] = $datetme;
        $NameDictModel  = new GmNameDict();
        $where = [
            'id'     => $id,
            'status' => GmNameDict::STATUS_YES
        ];
        $detail = $NameDictModel->fetchOne([], $where);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '您编辑的记录不存在或已经删除');
        }
        $ok = $NameDictModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '编辑失败,请稍候刷新重试!');
        }
    }

    /**
     * 删除。
     *
     * @param  int  $adminId  管理员 ID。
     * @param  int  $id       记录 ID。
     *
     * @return void
     */
    public static function delete($adminId, $id)
    {
        $where = [
            'id'     => $id,
            'status' => GmNameDict::STATUS_YES
        ];
        $NameDictModel = new GmNameDict();
        $detail = $NameDictModel->fetchOne([], $where);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '您删除的记录不存在或已经删除');
        }
        $data = [
            'status' => GmNameDict::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $status = $NameDictModel->update($data, $where);
        if (!$status) {
            Core::exception(STATUS_ERROR, '删除失败,请稍候刷新重试!');
        }
    }

    /**
     * 缓存重置。
     *
     * @param  int  $id  记录 ID。
     *
     * @return void
     */
    public static function resetCache()
    {
        $data = [
            'method' => 'game.intitle.reset.cache'
        ];
        $request = new Request();
        $result = $request->send($data);
        if ($result['code'] != STATUS_SUCCESS) {
            Core::exception(STATUS_SERVER_ERROR, '缓存重置失败');
        }
    }
}