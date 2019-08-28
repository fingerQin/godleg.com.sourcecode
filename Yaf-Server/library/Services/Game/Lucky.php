<?php
/**
 * 抽奖业务封装。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use finger\Database\Db;
use Utils\YCore;
use Utils\YCache;
use Utils\YString;
use Models\GmLuckyGoods;
use Models\GmLuckyPrize;
use Models\GoldConsume;
use Services\Gold\Gold;

class Lucky extends \Services\AbstractBase
{
    /**
     * 每次抽奖花费金币数量。
     *
     * @var int
     */
    public static $betGoldVal = 100;

    /**
     * 获取中奖奖品。
     *
     * @param  int  $isHasNo  是否包含未中奖的奖品项。
     *
     * @return array
     */
    public static function rewards($isHasNo = true)
    {
        $LuckyGoodsModel = new GmLuckyGoods();
        $columns = [
            'goods_name', 'image_url', 'reward_val'
        ];
        return $LuckyGoodsModel->fetchAll($columns, [], 0, 'id ASC');
    }

    /**
     * 用户发起抽奖。
     *
     * @param  int  $userId  用户ID。
     *
     * @return array
     */
    public static function do($userId)
    {
        $isOk = true; // 是否中奖。
        // [1] 取当前中奖的产品信息。
        $LuckyGoodsModel = new GmLuckyGoods();
        $luckyGoodsList  = $LuckyGoodsModel->fetchAll();
        $randValue       = mt_rand(1, 10000);
        $prizeInfo       = []; // 保存抽中的奖品信息。
        foreach ($luckyGoodsList as $item) {
            if ($randValue >= $item['min_range'] && $randValue <= $item['max_range']) {
                $prizeInfo = $item;
            }
        }
        // [2] 开启事务并扣除抽奖花费的金币。
        Db::beginTransaction();
        $gold =  Gold::consume($userId, self::$betGoldVal, GoldConsume::CONSUME_TYPE_CUT, GoldConsume::CONSUME_CODE_LUCKY_CUT);
        if ($prizeInfo['reward_val'] == 0) {
            $isOk = false;
            self::writeLuckyPrizeRecord($userId, '未中奖', 0, $randValue);
        }
        // [3] 判断中奖的奖品当天的中奖次数。
        $luckyGoodsTimeKey = "lucky_goods_time_{$prizeInfo['id']}";
        $cacheKey          = "lucky_goods_{$prizeInfo['id']}";
        $cacheVal          = YCache::get($cacheKey);
        if ($cacheVal === false) {
            YCache::set($luckyGoodsTimeKey, time());
            YCache::set($cacheKey, 1);
            $gold = Gold::consume($userId, $prizeInfo['reward_val'], GoldConsume::CONSUME_TYPE_ADD, GoldConsume::CONSUME_CODE_LUCKY_ADD);
            self::writeLuckyPrizeRecord($userId, $prizeInfo['goods_name'], $prizeInfo['reward_val'], $randValue);
        } else {
            $lastEndTime    = strtotime(date('Y-m-d 00:00:00', time()));
            $luckyGoodsTime = YCache::get($luckyGoodsTimeKey);
            if ($luckyGoodsTime > $lastEndTime) { // 当天。
                if ($cacheVal >= $prizeInfo['day_max']) { // 超过了奖品当天允许抽中的数量。
                    $isOk = false;
                    self::writeLuckyPrizeRecord($userId, '未中奖', GmLuckyGoods::GOODS_TYPE_NO, 0, $randValue);
                } else {
                    YCache::set($cacheKey, $cacheVal+1);
                    YCache::set($luckyGoodsTimeKey, time());
                    $gold =  Gold::consume($userId, $prizeInfo['reward_val'], GoldConsume::CONSUME_TYPE_ADD, GoldConsume::CONSUME_CODE_LUCKY_ADD);
                    self::writeLuckyPrizeRecord($userId, $prizeInfo['goods_name'], $prizeInfo['reward_val'], $randValue);
                }
            } else { // 昨天。
                YCache::set($cacheKey, 1);
                YCache::set($luckyGoodsTimeKey, time());
                $gold =  Gold::consume($userId, self::$betGoldVal, GoldConsume::CONSUME_TYPE_ADD, GoldConsume::CONSUME_CODE_LUCKY_ADD);
                self::writeLuckyPrizeRecord($userId, $prizeInfo['goods_name'],  $prizeInfo['reward_val'], $randValue);
            }
        }
        Db::commit();
        return [
            'is_ok'      => $isOk,
            'reward_id'  => $prizeInfo['id'],
            'goods_name' => $prizeInfo['goods_name'],
            'gold'       => $gold
        ];
    }

    /**
     * 写入用户抽奖记录。
     * 
     * -- 如果中奖是金币，则奖励是立即发送。
     * 
     * @param  int     $userId     用户ID。
     * @param  string  $goodsName  奖品名称。
     * @param  int     $rewardVal  奖励金币数量。
     * @param  int     $rangeVal   随机值。
     * @return void
     */
    private static function writeLuckyPrizeRecord($userId, $goodsName, $rewardVal, $rangeVal)
    {
        $datetime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $LuckyPrizeModel = new GmLuckyPrize();
        $data = [
            'userid'     => $userId,
            'goods_name' => $goodsName,
            'reward_val' => $rewardVal,
            'range_val'  => $rangeVal,
            'status'     => GmLuckyPrize::STATUS_YES,
            'c_time'     => $datetime
        ];
        $ok = $LuckyPrizeModel->insert($data);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 获取用户中奖记录。
     * @param  int     $userId     用户ID。
     * @param  string  $goodsName  奖品名称。
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * @return array
     */
    public static function records($userId, $page = 1, $count = 20)
    {
        $offset  = self::getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE userid = :userid AND status = :status';
        $params  = [
            ':status' => GmLuckyPrize::STATUS_YES,
            ':userid' => $userId
        ];
        $orderBy   = ' ORDER BY id DESC ';
        $sql       = "SELECT COUNT(1) AS count FROM gm_lucky_prize {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM gm_lucky_prize {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
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
     * 获取最新中奖记录。
     *
     * @param  int  $count  要取的记录条数。
     *
     * @return array
     */
    public static function getNewestRecords($count = 20)
    {
        $fromTable = ' FROM gm_lucky_prize AS a INNER JOIN finger_user AS b ON(a.userid=b.userid) ';
        $columns   = ' b.mobile, b.nickname, a.goods_name, a.c_time ';
        $where     = ' WHERE a.status = :status AND a.reward_val > 0 ';
        $params    = [
            ':status' => GmLuckyGoods::STATUS_YES
        ];
        $orderBy = ' ORDER BY a.id DESC ';
        $sql     = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT 0,{$count}";
        $list    = Db::all($sql, $params);
        foreach ($list as $k => $v) {
            $v['mobile'] = YString::asterisk($v['mobile'], 2, 5);
            $v['c_time'] = substr($v['c_time'], 5, 11);
            $list[$k]    = $v;
        }
        return $list;
    }
}