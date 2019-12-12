<?php
/**
 * 竞猜活动管理。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Core;
use finger\Date;
use finger\Validator;
use finger\Database\Db;
use Models\GmGuess;
use Models\GmGuessRecord;

class Guess extends \Services\AbstractBase
{
    /**
     * 选项。
     * @var array
     */
    public static $options = [
        'A' => 'A 选项',
        'B' => 'B 选项',
        'C' => 'C 选项',
        'D' => 'D 选项',
        'E' => 'E 选项'
    ];

    /**
     * 投注金币等级。
     * @var array
     */
    public static $goldLevel = [
        100,
        200,
        500
    ];

    /**
     * 获取竞猜详情。
     * @param  int  $guessId  竞猜ID。
     * @return array
     */
    public static function detail($guessId)
    {
        $where = [
            'guessid' => $guessId,
            'status'  => GmGuess::STATUS_YES
        ];
        $columns = [
            'guessid', 'title', 'image_url', 'option_data', 'deadline', 'is_open', 'open_result'
        ];
        $guessModel = new GmGuess();
        $guessInfo  = $guessModel->fetchOne($columns, $where);
        if (empty($guessInfo)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动不存在');
        }
        $guessInfo['deadline']    = Date::formatDateTime($guessInfo['deadline']);
        $guessInfo['option_data'] = json_decode($guessInfo['option_data'], true);
        return $guessInfo;
    }

    /**
     * 管理后台获取竞猜活动参与记录。
     * @param  int     $guessId      竞猜ID。
     * @param  string  $mobile       用户手机号。
     * @param  int     $prizeStatus  中奖状态:0开奖中、1已中奖、2未中奖。
     * @param  int     $page         当前页码。
     * @param  int     $count        每页显示条数。
     * @return array
     */
    public static function records($guessId = -1, $mobile = '', $prizeStatus = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_guess_record AS a INNER JOIN finger_user AS b ON(a.userid=b.userid) ';
        $columns   = ' a.guessid, a.userid, a.bet_gold, a.prize_status, a.prize_money, a.c_time, b.mobile, b.nickname ';
        $where     = ' WHERE a.status = :status ';
        $params    = [
            ':status' => GmGuess::STATUS_YES
        ];
        if ($guessId != -1) {
            $where .= ' AND a.guessid = :guessid ';
            $params[':guessid'] = $guessId;
        }
        if (strlen($mobile) > 0) {
            $where   .= ' AND b.mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if ($prizeStatus != -1) {
            $where .= ' AND a.prize_status = :prize_status ';
            $params[':prize_status'] = $prizeStatus;
        }
        $orderBy = ' ORDER BY id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['prize_status'] = GmGuessRecord::$prizeStatusDict[$item['prize_status']];
            $list[$key]           = $item;
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
     * 管理后台获取竞猜活动列表。
     * @param  string  $title      活动标题。
     * @param  string  $startTime  创建时间开始。
     * @param  string  $endTime    创建时间截止。
     * @param  int     $isOpen     是否开奖。
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * @return array
     */
    public static function list($title = '', $startTime = '', $endTime = '', $isOpen = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_guess ';
        $columns   = ' guessid, title, image_url, deadline, is_open, open_result, total_people, prize_people,'
                   . ' total_bet_gold, total_prize_gold, u_time, c_time ';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmGuess::STATUS_YES
        ];
        if (strlen($title) > 0) {
            $where .= ' AND title LIKE :title ';
            $params[':title'] = "%{$title}%";
        }
        if (strlen($startTime) > 0) {
            $where .= ' AND c_time >= :start_time ';
            $params[':start_time'] = strtotime($startTime);
        }
        if (strlen($endTime) > 0) {
            $where .= ' AND c_time <= :end_time ';
            $params[':end_time'] = $endTime;
        }
        if ($isOpen != -1) {
            $where .= ' AND is_open = :is_open ';
            $params[':is_open'] = $isOpen;
        }
        $orderBy = ' ORDER BY guessid DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['deadline']      = Date::formatDateTime($item['deadline']);
            $item['modified_time'] = Date::formatDateTime($item['u_time']);
            $list[$key]            = $item;
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
     * 添加竞猜活动。
     * -- Example start --
     * $options_data = [
     *      [
     *          'op_title' => '选项标题',
     *          'op_odds'  => '选项赔率'
     *      ],
     *      [
     *          'op_title' => '选项标题',
     *          'op_odds'  => '选项赔率'
     *      ],
     *      ......
     * ];
     * -- Example end --
     * @param  int     $adminId      管理员ID。
     * @param  string  $title        竞猜标题。
     * @param  string  $imageUrl     竞猜关联图片。
     * @param  array   $optionsData  竞猜选项数据。
     * @param  string  $deadline     活动参与截止日期。
     * @param  string  $openResult   开奖结果。
     * @return void
     */
    public static function add($adminId, $title, $imageUrl, $optionsData, $deadline, $openResult = '')
    {
        if (strlen($title) === 0) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜标题必须填写');
        }
        if (!Validator::is_len($title, 1, 255, true)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动标题必须1至255个字符 ');
        }
        if (strlen($imageUrl) === 0) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动图片必须上传');
        }
        if (!Validator::is_len($imageUrl, 1, 100, true)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动图片链接过长 ');
        }
        if (strlen($deadline) === 0) {
            Core::exception(STATUS_SERVER_ERROR, '活动参与截止日期必须填写');
        }
        if (!Validator::is_date($deadline)) {
            Core::exception(STATUS_SERVER_ERROR, '活动参与截止日期格式不正确');
        }
        if (empty($optionsData)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动选项必须设置');
        }
        if (strlen($openResult) > 0) {
            $openResult = strtoupper($openResult);
            if (!array_key_exists($openResult, self::$options)) {
                Core::exception(STATUS_SERVER_ERROR, '竞猜结果数据异常');
            }
        } else {
            $openResult = '';
        }
        foreach ($optionsData as $opk => $item) {
            if (strlen($item['op_title']) === 0 && strlen($item['op_odds']) === 0) {
                continue;
            }
            if (!isset($item['op_title']) || strlen($item['op_title']) === 0) {
                Core::exception(STATUS_SERVER_ERROR, '选项标题必须填写');
            }
            if (!Validator::is_len($item['op_title'], 1, 20, true)) {
                Core::exception(STATUS_SERVER_ERROR, '选项标题不能超过20个字符');
            }
            if (!isset($item['op_odds'])) {
                Core::exception(STATUS_SERVER_ERROR, '选项赔率必须设置');
            }
            if (!Validator::is_float($item['op_odds'])) {
                Core::exception(STATUS_SERVER_ERROR, '选项赔率必须是小数');
            }
        }
        $datetime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $data = [
            'title'        => $title,
            'image_url'    => $imageUrl,
            'option_data'  => json_encode($optionsData, JSON_UNESCAPED_UNICODE),
            'deadline'     => $deadline,
            'status'       => GmGuess::STATUS_YES,
            'open_result'  => $openResult,
            'c_by'         => $adminId,
            'u_time'       => $datetime,
            'c_time'       => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $GuessModel = new GmGuess();
        $ok = $GuessModel->insert($data);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑竞猜活动。
     * -- Example start --
     * $options_data = [
     *      [
     *          'op_title' => '选项标题',
     *          'op_odds'  => '选项赔率'
     *      ],
     *      [
     *          'op_title' => '选项标题',
     *          'op_odds'  => '选项赔率'
     *      ],
     *      ......
     * ];
     * -- Example end --
     * @param  int     $adminId      管理员ID。
     * @param  int     $guessId      竞猜活动ID。
     * @param  string  $title        竞猜标题。
     * @param  int     $isOpen       是否开奖。
     * @param  string  $imageUrl     竞猜关联图片。
     * @param  array   $optionsData  竞猜选项数据。
     * @param  string  $deadline     活动参与截止日期。
     * @param  string  $openResult   开奖结果。
     * @return void
     */
    public static function edit($adminId, $guessId, $title, $imageUrl, $isOpen, $optionsData, $deadline, $openResult = '')
    {
        if (strlen($title) === 0) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜标题必须填写');
        }
        if (!Validator::is_len($title, 1, 255, true)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动标题必须1至255个字符 ');
        }
        if (strlen($imageUrl) === 0) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动图片必须上传');
        }
        if (!Validator::is_len($imageUrl, 1, 100, true)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动图片链接过长 ');
        }
        if (strlen($deadline) === 0) {
            Core::exception(STATUS_SERVER_ERROR, '活动参与截止日期必须填写');
        }
        if (!Validator::is_date($deadline)) {
            Core::exception(STATUS_SERVER_ERROR, '活动参与截止日期格式不正确');
        }
        if (empty($optionsData)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动选项必须设置');
        }
        if (strlen($openResult) > 0) {
            $openResult = strtoupper($openResult);
            if (!array_key_exists($openResult, self::$options)) {
                Core::exception(STATUS_SERVER_ERROR, '竞猜结果数据异常');
            }
        } else {
            $openResult = '';
        }

        foreach ($optionsData as $item) {
            if (strlen($item['op_title']) === 0 && strlen($item['op_odds']) === 0) {
                continue;
            }
            if (!isset($item['op_title']) || strlen($item['op_title']) === 0) {
                Core::exception(STATUS_SERVER_ERROR, '选项标题必须填写');
            }
            if (!Validator::is_len($item['op_title'], 1, 20, true)) {
                Core::exception(STATUS_SERVER_ERROR, '选项标题不能超过20个字符');
            }
            if (!isset($item['op_odds'])) {
                Core::exception(STATUS_SERVER_ERROR, '选项赔率必须设置');
            }
            if (!Validator::is_float($item['op_odds'])) {
                Core::exception(STATUS_SERVER_ERROR, '选项赔率必须是小数');
            }
        }
        $where = [
            'guessid' => $guessId,
            'status'  => GmGuess::STATUS_YES
        ];
        $GuessModel = new GmGuess();
        $guessInfo  = $GuessModel->fetchOne([], $where);
        if (empty($guessInfo)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动不存在');
        }
        $data = [
            'title'       => $title,
            'image_url'   => $imageUrl,
            'option_data' => json_encode($optionsData, JSON_UNESCAPED_UNICODE),
            'deadline'    => $deadline,
            'is_open'     => $isOpen ? 1 : 0,
            'status'      => GmGuess::STATUS_YES,
            'open_result' => $openResult,
            'u_by'        => $adminId,
            'u_time'      => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $ok = $GuessModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除竞猜活动。
     * @param  int  $adminId  管理员ID。
     * @param  int  $guessId  竞猜活动ID。
     * @return void
     */
    public static function delete($adminId, $guessId)
    {
        $where = [
            'guessid' => $guessId,
            'status'  => GmGuess::STATUS_YES
        ];
        $GuessModel = new GmGuess();
        $guessInfo  = $GuessModel->fetchOne([], $where);
        if (empty($guessInfo)) {
            Core::exception(STATUS_SERVER_ERROR, '竞猜活动不存在');
        }
        $data = [
            'status' => GmGuess::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $ok = $GuessModel->update($data, $where);
        if (!$ok) {
            Core::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }
}