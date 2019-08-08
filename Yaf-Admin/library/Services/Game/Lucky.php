<?php
/**
 * 大转盘抽奖业务封装。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Services\Game;

use Utils\YCore;
use finger\Validator;
use finger\Database\Db;
use Models\GmLuckyGoods;
use Models\GmLuckyPrize;

class Lucky extends \Services\AbstractBase
{
    /**
     * 每次抽奖花费金币数量。
     *
     * @var int
     */
    public static $betGoldVal = 100;
    
    /**
     * 设置抽奖奖品。
     * -- Example start --
     * $goods = [
     *      [
     *          'goods_name' => '奖品名称',
     *          'day_max'    => '每天中奖最大次数。0代表不限制',
     *          'min_range'  => '随机最小概率值',
     *          'max_range'  => '随机最大概率值',
     *          'reward_val' => '奖励的数据。实物与未中奖为0。',
     *          'image_url'  => '奖品图片'
     *      ],
     *      ......
     * ];
     * -- Example end --
     * @param  int    $adminId  管理员ID。
     * @param  array  $goods    奖品列表。奖品格子只有八个。也就是说奖品也只能设置八个。
     * @return void
     */
    public static function setGoods($adminId, $goods)
    {
        if (count($goods) !== 8) {
            YCore::exception(STATUS_SERVER_ERROR, '奖品必须8个');
        }
        Db::execute('TRUNCATE TABLE gm_lucky_goods');
        Db::beginTransaction();
        foreach ($goods as $item) {
            if (!Validator::is_len($item['goods_name'], 1, 50, true)) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '奖品名称长度不能大于50个字符');
            }
            if (!Validator::is_number_between($item['day_max'], 0, 1000000)) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '奖品每天的中奖最大次数不能超过1000000次');
            }
            if (!Validator::is_number_between($item['min_range'], 1, 1000000)) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '随机最小概率值不能超过100000');
            }
            if (!Validator::is_number_between($item['max_range'], 1, 1000000)) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '随机最大概率值不能超过100000');
            }
            if (strlen($item['image_url']) === 0) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '奖品图片必须设置');
            }
            if (!Validator::is_len($item['image_url'], 1, 100, true)) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '图片长度不能超过100个字符');
            }
            if (!Validator::is_integer($item['reward_val']) || !Validator::is_number_between($item['reward_val'], 0, 100000)) {
                Db::rollBack();
                YCore::exception(STATUS_SERVER_ERROR, '奖励的数值不正确');
            }
            $datetime        = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);;
            $item['c_by']    = $adminId;
            $item['c_time']  = $datetime;
            $LuckyGoodsModel = new GmLuckyGoods();
            $ok = $LuckyGoodsModel->insert($item);
            if (!$ok) {
                Db::rollBack();
                YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
            }
        }
        Db::commit();
    }

    /**
     * 获取中奖奖品。
     *
     * @return array
     */
    public static function getGoodsList()
    {
        $columns = 'a.id, a.day_max, a.min_range, a.max_range, a.goods_name, a.image_url,'
                 . 'a.c_time, a.reward_val, b.adminid, b.real_name, b.mobile';
        $sql     = "SELECT {$columns} FROM gm_lucky_goods AS a INNER JOIN "
                 . "finger_admin_user AS b ON(a.c_by=b.adminid) ORDER BY a.id ASC";
        return Db::all($sql, []);
    }

    /**
     * 获取抽奖记录详情。
     * @param  int  $id  抽奖记录ID。
     * @return array
     */
    public static function detail($id)
    {
        $LuckyPrizeModel = new GmLuckyPrize();
        $columns = [
            'id', 'goods_name', 'range_val'
        ];
        $result = $LuckyPrizeModel->fetchOne($columns, ['id' => $id, 'status' => GmLuckyPrize::STATUS_YES]);
        if (empty($result)) {
            YCore::exception(STATUS_SERVER_ERROR, '抽奖记录不存在');
        }
        return $result;
    }

    /**
     * 获取用户抽奖记录。
     * 
     * @param  string  $mobile     手机号码。
     * @param  string  $goodsName  奖品名称。
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * @return array
     */
    public static function records($mobile = '', $goodsName = '', $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM gm_lucky_prize AS a INNER JOIN finger_user AS b ON(a.userid=b.userid) ';
        $columns   = ' a.id,a.goods_name,a.range_val,a.reward_val,a.c_time,b.mobile,b.nickname ';
        $where     = ' WHERE 1 ';
        $params    = [];
        if (strlen($mobile) !== 0) {
            $where .= ' AND b.mobile = :mobile ';
            $params[':mobile'] = $mobile;
        }
        if (strlen($goodsName) !== 0) {
            $where .= ' AND a.goods_name LIKE :goods_name ';
            $params[':goods_name'] = "%{$goodsName}%";
        }
        $orderBy   = ' ORDER BY a.id DESC ';
        $sql       = "SELECT COUNT(1) AS count FROM gm_lucky_prize {$where}";
        $total     = Db::count($sql, $params);
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
}