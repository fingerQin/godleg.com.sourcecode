<?php
/**
 * 测前世配置管理。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use Utils\YCore;
use finger\Database\Db;
use ApiTools\Request;
use Models\GmPrelife;

class PreLife extends \Services\AbstractBase
{
    /**
     * 前世身份类别字典。
     *
     * @var array
     */
    public static $typeDict = [
        GmPreLife::TYPE_OFFICER  => '士',
        GmPreLife::TYPE_PEASANT  => '农',
        GmPreLife::TYPE_WORKERS  => '工',
        GmPreLife::TYPE_MERCHANT => '商',
    ];

    /**
     * 优先级字典。
     *
     * @var array
     */
    public static $priorityDict = [
        GmPreLife::PRIORITY_HIGH   => '高',
        GmPreLife::PRIORITY_MIDDLE => '中',
        GmPreLife::PRIORITY_LOW    => '低'
    ];

    /**
     * 列表。
     *
     * @param  string  $title     身份。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  int     $type      类型：1-士、2-农、3-工、4-商。
     * @param  int     $page      页码。
     * @param  int     $count     每页显示条数。
     *
     * @return array
     */
    public static function list($title = '', $priority = -1, $type = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_prelife ';
        $columns   = ' id, priority, type, title, intro, c_time, u_time ';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmPreLife::STATUS_YES
        ];
        if (strlen($title) > 0) {
            $where .= ' AND title = :title ';
            $params[':title'] = $title;
        }
        if ($type != -1) {
            $where .= ' AND type = :type ';
            $params[':type'] = $type;
        }
        if ($priority != -1) {
            $where .= ' AND priority = :priority ';
            $params[':priority'] = $priority;
        }
        $orderBy = ' ORDER BY id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['type']     = self::$typeDict[$item['type']];
            $item['priority'] = self::$priorityDict[$item['priority']];
            $list[$key]       = $item;
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
        $GmPreLife = new GmPreLife();
        $columns = ['id', 'priority', 'type', 'title', 'intro'];
        $where = [
            'id'     => $id,
            'status' => GmPreLife::STATUS_YES
        ];
        $detail = $GmPreLife->fetchOne($columns, $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        return $detail;
    }

    /**
     * 添加。
     *
     * @param  int     $adminId   管理员 ID。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  string  $title     身份名称。
     * @param  int     $type      身份类型：1-士、2-农、3-工、4-商
     * @param  string  $intro     生平介绍。
     *
     * @return array
     */
    public static function add($adminId, $priority, $title, $type, $intro)
    {
        $rules = [
            'priority' => '优先级|require|number_between:1:3',
            'type'     => '类型|require|integer|number_between:1:4',
            'title'    => '身份|require|len:1:20:1|chinese',
            'intro'    => '生平简短介绍|require|len:0:255:1'
        ];
        $data = [
            'priority' => $priority,
            'type'     => $type,
            'title'    => $title,
            'intro'    => $intro
        ];
        $datetme        = date('Y-m-d H:i:s', time());
        $data['c_by']   = $adminId;
        $data['c_time'] = $datetme;
        $data['u_time'] = $datetme;
        $GmPreLife   = new GmPreLife();
        $ok = $GmPreLife->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试!');
        }
    }

    /**
     * 编辑。
     *
     * @param  int     $adminId   管理员 ID。
     * @param  int     $id        记录 ID。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  string  $title     身份名称。
     * @param  int     $type      身份类型：1-士、2-农、3-工、4-商
     * @param  string  $intro     生平介绍。
     *
     * @return array
     */
    public static function edit($adminId, $id, $priority, $title, $type, $intro)
    {
        $rules = [
            'priority' => '优先级|require|number_between:1:3',
            'type'     => '类型|require|integer|number_between:1:4',
            'title'    => '身份|require|len:1:20:1|chinese',
            'intro'    => '生平简短介绍|require|len:0:255:1'
        ];
        $data = [
            'priority' => $priority,
            'type'     => $type,
            'title'    => $title,
            'intro'    => $intro
        ];
        $datetme        = date('Y-m-d H:i:s', time());
        $data['u_by']   = $adminId;
        $data['u_time'] = $datetme;
        $GmPreLife   = new GmPreLife();
        $where = [
            'id'     => $id,
            'status' => GmPreLife::STATUS_YES
        ];
        $detail = $GmPreLife->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '您编辑的记录不存在或已经删除');
        }
        $ok = $GmPreLife->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '编辑失败,请稍候刷新重试!');
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
            'status' => GmPreLife::STATUS_YES
        ];
        $GmPreLife = new GmPreLife();
        $detail = $GmPreLife->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '您删除的记录不存在或已经删除');
        }
        $data = [
            'status' => GmPreLife::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $status = $GmPreLife->update($data, $where);
        if (!$status) {
            YCore::exception(STATUS_ERROR, '删除失败,请稍候刷新重试!');
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
            'method' => 'game.pre.life.reset.cache',
            'v'      => '1.0.0'
        ];
        $request = new Request();
        $result = $request->send($data);
        if ($result['code'] != STATUS_SUCCESS) {
            YCore::exception(STATUS_SERVER_ERROR, '缓存重置失败');
        }
    }
}