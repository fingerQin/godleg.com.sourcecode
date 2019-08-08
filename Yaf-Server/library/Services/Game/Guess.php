<?php
/**
 * 竞猜活动管理。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Database\Db;
use Utils\YCore;
use Models\GmGuess;
use Models\GmGuessRecord;
use Models\GoldConsume;
use Services\Gold\Gold;

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
     * 获取用户竞猜记录。
     *
     * @param  int  $userId  用户ID。
     * @param  int  $page    当前页码。
     * @param  int  $count   每页显示条数。
     *
     * @return array
     */
    public static function records($userId, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_guess_record AS a INNER JOIN gm_guess AS b ON(a.guessid=b.guessid) ';
        $columns   = ' b.guessid, b.title, b.is_open, b.open_result, b.deadline, b.image_url, '
                   . ' a.bet_gold, a.answer_index, a.prize_status, a.prize_money, a.c_time ';
        $where     = ' WHERE a.userid = :userid AND a.status = :status ';
        $params    = [
            ':userid' => $userId,
            ':status' => GmGuess::STATUS_YES
        ];
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取系统竞猜活动。
     * 
     * @param  int  $page   当前页码。
     * @param  int  $count  每页显示条数。
     * 
     * @return array
     */
    public static function list($page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_guess ';
        $columns   = ' guessid, title, image_url, deadline, option_data ';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmGuess::STATUS_YES
        ];
        $orderBy   = ' ORDER BY guessid DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 用户竞猜。
     *
     * @param  int     $userId       用户ID。
     * @param  int     $guessId      竞猜活动ID。
     * @param  string  $optionIndex  用户选择的选项。
     * @param  int     $betGold      投注金币。
     *
     * @return int 用户当前操作之后剩余的金币。
     */
    public static function do($userId, $guessId, $optionIndex, $betGold)
    {
        $datetime    = date('Y-m-d H:i:s', time());
        $optionIndex = \strtoupper($optionIndex);
        if (!in_array($betGold, self::$goldLevel)) {
            YCore::exception(STATUS_SERVER_ERROR, '投注金币数错误');
        }
        $GuessModel  = new GmGuess();
        $guessDetail = $GuessModel->fetchOne([], ['guessid' => $guessId, 'status' => GmGuess::STATUS_YES]);
        if (empty($guessDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '竞猜活动不存在');
        }
        if ($guessDetail['deadline'] < $datetime) {
            YCore::exception(STATUS_SERVER_ERROR, '活动已经结束');
        }
        $guessOptions = json_decode($guessDetail['option_data'], true);
        if (!isset($guessOptions[$optionIndex]) || strlen($guessOptions[$optionIndex]['op_title']) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '您选择的答案错误');
        }
        Db::beginTransaction();
        try {
            $userGold = Gold::consume($userId, $betGold, 
            GoldConsume::CONSUME_TYPE_CUT, 
            GoldConsume::CONSUME_CODE_GUESS_CUT);
        } catch (\Exception $e) {
            Db::rollBack();
            YCore::exception($e->getCode(), $e->getMessage());
        }
        // 计算用户所投选项投中之后的奖励金额。
        $odds             = $guessOptions[$optionIndex]['op_odds']; // 赔率。
        $prizeMoney       = $odds * $betGold;
        $GuessRecordModel = new GmGuessRecord();
        $data = [
            'guessid'      => $guessId,
            'userid'       => $userId,
            'bet_gold'     => $betGold,
            'answer_index' => $optionIndex,
            'prize_status' => GmGuessRecord::PRIZE_STATUS_WAIT,
            'prize_money'  => $prizeMoney,
            'status'       => GmGuess::STATUS_YES,
            'c_time'       => $datetime
        ];
        $ok = $GuessRecordModel->insert($data);
        if (!$ok) {
            Db::rollBack();
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        Db::commit();
        return $userGold;
    }

    /**
     * 派奖程序。
     * 
     * -- 建议每天凌晨1点执行。等以后有足够多的服务器资源，可以开启每1小时执行一次。
     * -- 派奖程序采用多进程处理。每个进程对竞猜ID取模分批处理。
     * 
     * @param  int  $totalBat  批次总数/进程总数。必须大于1。否则，没有必要使用多进程。
     * @param  int  $num       进程编号。
     *
     * @return void
     */
    public static function sendPrize($totalBat, $num)
    {
        // [1] 
        $where = [
            'reward_send_status' => ['IN', [GmGuess::SEND_STATUS_NO, GmGuess::SEND_STATUS_ING]],
            'is_open'            => GmGuess::YES
        ];
        $GmGuessModel = new GmGuess();
        $guessResult  = $GmGuessModel->fetchAll(['guessid', 'open_result'], $where, 0, 'guessid ASC');
        if (empty($guessResult)) {
            exit('ok');
        }
        // [2]
        $guessIds    = []; // 保存竞猜 ID 数组。
        $guessKeyVal = []; // 竞猜信息以竞猜 ID 为键值为详情组装。
        foreach ($guessResult as $guess) {
            $guessIds[] = $guess['guessid'];
            $guessKeyVal[$guess['guessid']] = $guess;
        }
        // [3] 循环读每批次的数据进行开奖处理。
        $GmGuessRecordModel = new GmGuessRecord();
        while(true) {
            $where = [
                'guessid' => ['IN', $guessIds]
            ];
            $whereInfo = $GmGuessRecordModel->parseWhereCondition($where);
            $sql       = "SELECT * FROM gm_guess_record WHERE " . $whereInfo['where'] 
                       . " AND status = :status AND id%{$totalBat}=$num AND prize_status = :prize_status"
                       . " ORDER BY id ASC LIMIT 20";
            $params    = $whereInfo['params'];
            $params[':status']       = GmGuessRecord::STATUS_YES;
            $params[':prize_status'] = GmGuessRecord::PRIZE_STATUS_WAIT;
            $result = Db::all($sql, $params);
            if (!empty($result)) {
                try {
                    Db::beginTransaction();
                    $datetime = date('Y-m-d H:i:s', time());
                    foreach ($result as $item) {
                        $isOk = false; // 是否中奖。
                        if ($item['answer_index'] == $guessKeyVal[$item['guessid']]['open_result']) { // 中奖。
                            $isOk = true;
                            Gold::consume($item['userid'], $item['prize_money'], 
                            GoldConsume::CONSUME_TYPE_ADD,
                            GoldConsume::CONSUME_CODE_GUESS_ADD);
                        }
                        $sql = 'UPDATE gm_guess_record SET prize_status = :prize_status'
                             . ',u_time = :u_time WHERE id = :id';
                        $params = [
                            ':prize_status' => $isOk ? GmGuessRecord::PRIZE_STATUS_OK : GmGuessRecord::PRIZE_STATUS_NO,
                            ':u_time'       => $datetime,
                            ':id'           => $item['id']
                        ];
                        Db::execute($sql, $params);
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollBack();
                }
            } else {
                break; // 当前已经没有可处理的投注记录。
            }
        }
        echo 'ok';
    }
}
