<?php
/**
 * 谜题库管理。
 * @author fingerQin
 * @date 2018-11-15
 */

namespace Services\Game;

use Utils\YCore;
use Utils\YCache;
use finger\Database\Db;
use ApiTools\Request;
use Models\GmRiddle;
use finger\Validator;

class Riddle extends \Services\AbstractBase
{
    /**
     * 来源字典。
     *
     * @var array
     */
    public static $sourceDict = [
        GmRiddle::SOURCE_SYSTEM => '系统创建',
        GmRiddle::SOURCE_USER   => '用户创建'
    ];

    /**
     * 优先级字典。
     *
     * @var array
     */
    public static $priorityDict = [
        GmRiddle::PRIORITY_HIGH   => '高',
        GmRiddle::PRIORITY_MIDDLE => '中',
        GmRiddle::PRIORITY_LOW    => '低'
    ];

    /**
     * 名字库列表。
     *
     * @param  string  $openid    开放 ID。
     * @param  int     $source    来源。
     * @param  int     $score     分值。大于此分值的数据。
     * @param  int     $priority  优先级。1-50%、2-49%、3-1%。
     * @param  int     $page      页码。
     * @param  int     $count     每页显示条数。
     *
     * @return array
     */
    public static function list($openid = '', $source = -1, $score = 0, $priority = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_riddle ';
        $columns   = ' id, openid, priority, score, question, question_img, answer, answer_img, source, c_time, u_time ';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmRiddle::STATUS_YES
        ];
        if (strlen($openid) > 0) {
            $where .= ' AND openid = :openid ';
            $params[':openid'] = $openid;
        }
        if ($source != -1) {
            $where .= ' AND source = :source ';
            $params[':source'] = $source;
        }
        if ($score > 0) {
            $where .= ' AND score > :source ';
            $params[':score'] = $score;
        }
        if ($priority != -1) {
            $where .= ' AND priority = :priority ';
            $params[':priority'] = $priority;
        }
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['source']   = self::$sourceDict[$item['source']];
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
        $GmRiddle = new GmRiddle();
        $columns     = ['id', 'openid', 'priority', 'score', 'question', 'question_img', 'answer', 'answer_img', 'source'];
        $where = [
            'id'     => $id,
            'status' => GmRiddle::STATUS_YES
        ];
        $detail = $GmRiddle->fetchOne($columns, $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '记录不存在或已经删除');
        }
        return $detail;
    }

    /**
     * 添加。
     *
     * @param  int     $adminId      管理员 ID。
     * @param  int     $priority     优先级。1-50%、2-49%、3-1%。
     * @param  int     $score        难度评分。
     * @param  string  $question     题目。
     * @param  string  $questionImg  题目图片。
     * @param  string  $answer       答案。
     * @param  string  $answerImg    答案图片链接。
     *
     * @return array
     */
    public static function add($adminId, $priority, $score, $question, $questionImg, $answer, $answerImg)
    {
        $rules = [
            'priority'     => '优先级|require|number_between:1:3',
            'score'        => '评分|require|integer|number_between:1:100',
            'question'     => '题目|require|len:1:255:1',
            'question_img' => '题目图片|len:0:100:1',
            'answer'       => '答案|require|len:1:255:1',
            'answer_img'   => '答案图片|require|len:0:100:1'
        ];
        $data = [
            'priority'     => $priority,
            'score'        => $score,
            'question'     => $question,
            'question_img' => $questionImg,
            'answer'       => $answer,
            'answer_img'   => $answerImg
        ];
        $datetme        = date('Y-m-d H:i:s', time());
        $data['openid'] = self::createOpenID();
        $data['source'] = GmRiddle::SOURCE_SYSTEM;
        $data['c_by']   = $adminId;
        $data['c_time'] = $datetme;
        $data['u_time'] = $datetme;
        $GmRiddle = new GmRiddle();
        $ok = $GmRiddle->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试!');
        }
    }

    /**
     * 创建一个 OpenID。
     *
     * @return string
     */
    protected static function createOpenID()
    {
        $datetime = date('YmdHi');
        $key      = "riddle-openid-{$datetime}";
        $redis    = YCache::getRedisClient();
        $incr     = $redis->incr($key);
        if ($incr == 1) {
            $redis->expire($key, 90);
        }
        return md5("{$key}-{$incr}");
    }

    /**
     * 编辑。
     *
     * @param  int     $adminId      管理员 ID。
     * @param  int     $id           记录 ID。
     * @param  int     $priority     优先级。1-50%、2-49%、3-1%。
     * @param  int     $score        难度评分。
     * @param  string  $question     题目。
     * @param  string  $questionImg  题目图片。
     * @param  string  $answer       答案。
     * @param  string  $answerImg    答案图片链接。
     *
     * @return array
     */
    public static function edit($adminId, $id, $priority, $score, $question, $questionImg, $answer, $answerImg)
    {
        $rules = [
            'priority'     => '优先级|require|number_between:1:3',
            'score'        => '评分|require|integer|number_between:1:100',
            'question'     => '题目|require|len:1:255:1',
            'question_img' => '题目图片|len:0:100:1',
            'answer'       => '答案|require|len:1:255:1',
            'answer_img'   => '答案图片|require|len:0:100:1'
        ];
        $data = [
            'priority'     => $priority,
            'score'        => $score,
            'question'     => $question,
            'question_img' => $questionImg,
            'answer'       => $answer,
            'answer_img'   => $answerImg
        ];
        Validator::valido($data, $rules);
        $datetme        = date('Y-m-d H:i:s', time());
        $data['u_by']   = $adminId;
        $data['u_time'] = $datetme;
        $GmRiddle = new GmRiddle();
        $where    = [
            'id'     => $id,
            'status' => GmRiddle::STATUS_YES
        ];
        $detail = $GmRiddle->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '您编辑的记录不存在或已经删除');
        }
        $ok = $GmRiddle->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '编辑失败,请稍候刷新重试!');
        }
        self::clearCache($detail['openid']);
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
            'status' => GmRiddle::STATUS_YES
        ];
        $GmRiddle = new GmRiddle();
        $detail = $GmRiddle->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '您删除的记录不存在或已经删除');
        }
        $data = [
            'status' => GmRiddle::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $status = $GmRiddle->update($data, $where);
        if (!$status) {
            YCore::exception(STATUS_ERROR, '删除失败,请稍候刷新重试!');
        }
        self::clearCache($detail['openid']);
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
            'method' => 'game.riddle.reset.cache'
        ];
        $request = new Request();
        $result  = $request->send($data);
        if ($result['code'] != STATUS_SUCCESS) {
            YCore::exception(STATUS_SERVER_ERROR, '缓存重置失败');
        }
    }

    /**
     * 清除缓存。
     *
     * @param  int  $openid  记录 ID。
     *
     * @return void
     */
    private static function clearCache($openid)
    {
        $data = [
            'method' => 'game.riddle.detail.clear.cache',
            'openid' => $openid
        ];
        $request = new Request();
        $result = $request->send($data);
        if ($result['code'] != STATUS_SUCCESS) {
            YCore::exception(STATUS_SERVER_ERROR, '缓存清除失败');
        }
    }
}