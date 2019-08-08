<?php
/**
 * 扑克王游戏。
 * -- 1、每局游戏有五张牌。3张普通牌,1张小王,1张大王。
 * -- 2、抽到普通牌输掉押注金币。
 * -- 3、抽到小王返还两部押注金币。
 * -- 4、抽到大王返还三倍押注金币。
 * -- 5、押注金币为固定三个档位：100、500、1000。
 * @author fingerQin
 * @date 2018-08-28
 */

namespace Services\Game;

use finger\Database\Db;
use Models\GmPokerKingRecord;

class PokerKing extends \Services\AbstractBase
{
    /**
     * 金币押注档次。
     * @var array
     */
    public static $moneyLevel = [
        100,
        500,
        1000
    ];

    /**
     * 扑克牌。
     * @var array
     */
    private static $pokers = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'
    ];

    /**
     * 五张扑克牌初始形式。
     * -- 数字4、5分别代表小王、大王。
     * @var array
     */
    private static $fivePokersInit = [
        1, 2, 3, 4, 5
    ];

    /**
     * 管理后台获取扑克王游戏翻牌记录。
     * 
     * @param  string  $mobile     用户手机号。
     * @param  int     $pokerType  用户翻到的牌的类型：1大王、2小王、3普通牌。
     * @param  int     $isPrize    是否中奖:0-否、1-是
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * 
     * @return array
     */
    public static function records($mobile = '', $pokerType = -1, $isPrize = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_poker_king_record AS a INNER JOIN finger_user AS b ON(a.userid=b.userid) ';
        $columns   = ' a.id, a.userid, a.bet_gold, a.is_prize, a.prize_money, a.poker, a.pokers,'
                   . ' a.poker_type, a.c_time, b.mobile, b.nickname ';
        $where     = ' WHERE a.status = :status ';
        $params    = [
            ':status' => GmPokerKingRecord::STATUS_YES
        ];
        if (strlen($mobile) > 0) {
            $where   .= ' AND a.mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if ($isPrize != -1) {
            $where .= ' AND is_prize = :is_prize ';
            $params[':is_prize'] = $isPrize;
        }
        if ($pokerType != -1) {
            $where .= ' AND poker_type = :poker_type ';
            $params[':poker_type'] = $pokerType;
        }
        $orderBy = ' ORDER BY id DESC ';
        $sql     = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $total   = Db::count($sql, $params);
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $key => $item) {
            $item['pokers'] = json_decode($item['pokers'], true);
            $list[$key]     = $item;
        }
        $result = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }
}