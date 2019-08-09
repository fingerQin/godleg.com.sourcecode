<?php
/**
 * 彩票(双色球)游戏。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Utils\YDate;
use Models\GmLotteryResult;
use Models\GmLotteryBetRecord;
use Models\GmLotteryBetRecordNumber;

class Lottery extends \Services\AbstractBase
{
    /**
     * 单倍投注每手100金币。
     *
     * @var int
     */
    public static $singleGold = 100;

    /**
     * 获取彩票开奖结果。
     * 
     * @param  int  $page   当前页码。
     * @param  int  $count  每页显示条数。
     * 
     * @return array
     */
    public static function getLotteryResultList($page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_lottery_result ';
        $columns   = ' id, phase_sn, lottery_result, first_prize, second_prize, first_prize_count, '
                   . ' second_prize_count, lottery_time, c_time, last_time_residue_gold, curr_time_sell_gold';
        $where     = ' WHERE status = :status ';
        $params    = [
            ':status' => GmLotteryResult::STATUS_YES
        ];
        $orderBy = ' ORDER BY id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        $result  = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取彩票开奖结果详情。
     * 
     * @param  int  $id  彩票开奖结果ID。
     * 
     * @return array
     */
    public static function getLotteryResultDetail($id)
    {
        $lotteryResultModel = new GmLotteryResult();
        $columns = [
            'id', 'phase_sn', 'lottery_result', 'first_prize', 'second_prize',
            'first_prize_count', 'second_prize_count', 'lottery_time'
        ];
        $detail = $lotteryResultModel->fetchOne($columns, ['id' => $id, 'status' => 1]);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '开奖结果不存在');
        }
        $detail['lottery_time'] = YDate::formatDateTime($detail['lottery_time']);
        return $detail;
    }

    /**
     * 添加彩票开奖结果。
     *
     * @param  string  $phaseSn           彩票期次。
     * @param  string  $lotteryResult     开奖结果。
     * @param  int     $firstPrize        一等奖奖金。
     * @param  int     $secondPrize       二等奖奖金。
     * @param  int     $firstPrizeCount   一等奖中奖注数。
     * @param  int     $secondPrizeCount  二等奖中奖注数。
     * @param  string  $lotteryTime       开奖时间。
     * @param  int     $adminId           管理员ID。
     *
     * @return array
     */
    public static function addLotteryResult($phaseSn, $lotteryResult, $firstPrize, $secondPrize, $firstPrizeCount, $secondPrizeCount, $lotteryTime, $adminId)
    {
        $data = [
            'phase_sn'           => $phaseSn,
            'lottery_result'     => $lotteryResult,
            'first_prize'        => $firstPrize,
            'second_prize'       => $secondPrize,
            'first_prize_count'  => $firstPrizeCount,
            'second_prize_count' => $secondPrizeCount,
            'lottery_time'       => $lotteryTime
        ];
        $rules = [
            'phase_sn'           => '彩票期次|require',
            'lottery_result'     => '开奖结果|require',
            'first_prize'        => '一等奖奖金|require|integer',
            'second_prize'       => '二等奖奖金|require|integer',
            'first_prize_count'  => '一等奖中奖注数|require|integer',
            'second_prize_count' => '二等奖中奖注数|require|integer',
            'lottery_time'       => '彩票开奖时间|require|date:1'
        ];
        Validator::valido($data, $rules);
        $data['status'] = GmLotteryResult::STATUS_YES;
        $data['c_by']   = $adminId;
        $data['c_time'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $GmLotteryResultModel = new GmLotteryResult();
        $ok = $GmLotteryResultModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 编辑彩票开奖结果。
     *
     * @param  int     $id                记录ID。
     * @param  string  $phaseSn           彩票期次。
     * @param  string  $lotteryResult     开奖结果。
     * @param  int     $firstPrize        一等奖奖金。
     * @param  int     $secondPrize       二等奖奖金。
     * @param  int     $firstPrizeCount   一等奖中奖注数。
     * @param  int     $secondPrizeCount  二等奖中奖注数。
     * @param  string  $lotteryTime       开奖时间。
     * @param  int     $admin_id          管理员ID。
     *
     * @return void
     */
    public static function editLotteryResult($id, $phaseSn, $lotteryResult, $firstPrize, $secondPrize, $firstPrizeCount, $secondPrizeCount, $lotteryTime, $adminId)
    {
        $data = [
            'phase_sn'           => $phaseSn,
            'lottery_result'     => $lotteryResult,
            'first_prize'        => $firstPrize,
            'second_prize'       => $secondPrize,
            'first_prize_count'  => $firstPrizeCount,
            'second_prize_count' => $secondPrizeCount,
            'lottery_time'       => $lotteryTime,
        ];
        $rules = [
            'phase_sn'           => '彩票期次|require',
            'lottery_result'     => '开奖结果|require',
            'first_prize'        => '一等奖奖金|require|integer',
            'second_prize'       => '二等奖奖金|require|integer',
            'first_prize_count'  => '一等奖中奖注数|require|integer',
            'second_prize_count' => '二等奖中奖注数|require|integer',
            'lottery_time'       => '彩票开奖时间|require|date:1'
        ];
        Validator::valido($data, $rules);
        $LotteryResultModel = new GmLotteryResult();
        $where = [
            'id'     => $id,
            'status' => GmLotteryResult::STATUS_YES
        ];
        $detail = $LotteryResultModel->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '开奖记录不存在');
        }
        $data['u_by']   = $adminId;
        $data['u_time'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $ok = $LotteryResultModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除彩票开奖结果。
     * @param  int  $id       记录ID。
     * @param  int  $adminId  管理员ID。
     * @return void
     */
    public static function deleteLotteryResult($id, $adminId)
    {
        $LotteryResultModel = new GmLotteryResult();
        $where  = ['id' => $id, 'status' => GmLotteryResult::STATUS_YES];
        $detail = $LotteryResultModel->fetchOne([], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '彩票开奖结果不存在');
        }
        $data = [
            'status' => GmLotteryResult::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $ok = $LotteryResultModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 获取用户投注记录。
     *
     * @param  string  $mobile     手机号码。
     * @param  int     $betStatus  中奖状态。0待开奖、1已中奖、2未中奖。
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * @return array
     */
    public static function records($mobile = '', $betStatus = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_lottery_bet_record AS a INNER JOIN finger_user AS b ON(a.userid=b.userid) ';
        $columns   = ' a.betid,a.phase_sn,a.first_count,a.second_count,a.other_money,a.prize_status,a.bet_count, '
                   . ' a.bet_gold,a.bet_status,a.reward_gold,a.c_time,b.mobile,b.nickname ';
        $where     = ' WHERE 1 ';
        $params    = [];
        if (strlen($mobile) > 0) {
            $where .= ' AND b.mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if ($betStatus != -1) {
            $where .= ' AND a.bet_status = :bet_status ';
            $params[':bet_status'] = $betStatus;
        }
        $orderBy = ' ORDER BY a.betid DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['bet_records']      = self::getBetRecordNumber($item['betid']);
            $item['bet_status_lebal'] = GmLotteryBetRecord::$lotteryStatusLabel[$item['bet_status']];
            $list[$key]               = $item;
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
     * 获取投注记录关联的投注号码。
     *
     * @param  int  $betId  投注记录ID。
     * @return array
     */
    protected static function getBetRecordNumber($betId)
    {
        $betRecordMumberModel = new GmLotteryBetRecordNumber();
        $columns = [
            'bet_gold', 'bet_number'
        ];
        return $betRecordMumberModel->fetchAll($columns, ['betid' => $betId], 0, 'id ASC');
    }
}