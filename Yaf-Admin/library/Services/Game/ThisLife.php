<?php
/**
 * 测前世配置管理。
 * @author fingerQin
 * @date 2018-11-14
 */

namespace Services\Game;

use finger\Database\Db;
use ApiTools\Request;
use finger\Core;
use finger\Validator;
use Models\GmThisLife;

class ThisLife extends \Services\AbstractBase
{
    /**
     * 优先级字典。
     *
     * @var array
     */
    public static $priorityDict = [
        GmThisLife::PRIORITY_HIGH   => '高',
        GmThisLife::PRIORITY_MIDDLE => '中',
        GmThisLife::PRIORITY_LOW    => '低'
    ];

    /**
     * 列表。
     *
     * @param  string  $title     身份。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  int     $page      页码。
     * @param  int     $count     每页显示条数。
     *
     * @return array
     */
    public static function list($title = '', $priority = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_this_life ';
        $columns   = ' id, priority, score, title, intro, c_time, u_time ';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmThisLife::STATUS_YES
        ];
        if (strlen($title) > 0) {
            $where .= ' AND title = :title ';
            $params[':title'] = $title;
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
        $PreLifeModel = new GmThisLife();
        $columns = ['id', 'priority', 'score', 'title', 'intro'];
        $where = [
            'id'     => $id,
            'status' => GmThisLife::STATUS_YES
        ];
        $detail = $PreLifeModel->fetchOne($columns, $where);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        return $detail;
    }

    /**
     * 添加。
     *
     * @param  int     $adminId   管理员 ID。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  int     $score     身份优劣评分。
     * @param  string  $title     身份名称。
     * @param  string  $intro     身份介绍。
     *
     * @return array
     */
    public static function add($adminId, $priority, $score, $title, $intro)
    {
        $rules = [
            'priority' => '优先级|require|number_between:1:3',
            'score'    => '评分|require|integer|number_between:1:100',
            'title'    => '身份|require|len:1:20:1|chinese',
            'intro'    => '身份介绍|require|len:0:255:1'
        ];
        $data = [
            'priority' => $priority,
            'score'    => $score,
            'title'    => $title,
            'intro'    => $intro
        ];
        Validator::valido($data, $rules);
        $datetme = date('Y-m-d H:i:s', time());
        $data['c_by']   = $adminId;
        $data['c_time'] = $datetme;
        $data['u_time'] = $datetme;
        $GmThisLife  = new GmThisLife();
        $ok = $GmThisLife->insert($data);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试!');
        }
    }

    /**
     * 编辑。
     *
     * @param  int     $adminId   管理员 ID。
     * @param  int     $id        记录 ID。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  int     $score     身份优劣评分。
     * @param  string  $title     身份名称。
     * @param  string  $intro     身份介绍。
     *
     * @return array
     */
    public static function edit($adminId, $id, $priority, $score, $title, $intro)
    {
        $rules = [
            'priority' => '优先级|require|number_between:1:3',
            'score'    => '评分|require|integer|number_between:1:100',
            'title'    => '身份|require|len:1:20:1|chinese',
            'intro'    => '身份介绍|require|len:0:255:1'
        ];
        $data = [
            'priority' => $priority,
            'score'    => $score,
            'title'    => $title,
            'intro'    => $intro
        ];
        $datetme        = date('Y-m-d H:i:s', time());
        $data['u_by']   = $adminId;
        $data['u_time'] = $datetme;
        $GmThisLife  = new GmThisLife();
        $where = [
            'id'     => $id,
            'status' => GmThisLife::STATUS_YES
        ];
        $detail = $GmThisLife->fetchOne([], $where);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '您编辑的记录不存在或已经删除');
        }
        $ok = $GmThisLife->update($data, $where);
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
            'status' => GmThisLife::STATUS_YES
        ];
        $GmThisLife = new GmThisLife();
        $detail = $GmThisLife->fetchOne([], $where);
        if (empty($detail)) {
            Core::exception(STATUS_SERVER_ERROR, '您删除的记录不存在或已经删除');
        }
        $data = [
            'status' => GmThisLife::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $status = $GmThisLife->update($data, $where);
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
            'method' => 'game.this.life.reset.cache',
            'v'      => '1.0.0'
        ];
        $request = new Request();
        $result = $request->send($data);
        if ($result['code'] != STATUS_SUCCESS) {
            Core::exception(STATUS_SERVER_ERROR, '缓存重置失败');
        }
    }
}