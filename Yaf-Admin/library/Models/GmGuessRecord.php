<?php
/**
 * 竞猜活动表。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Models;

class GmGuessRecord extends AbstractBase
{
    /**
     * 中奖状态。
     */
    const PRIZE_STATUS_WAIT = 0; // 待开奖。
    const PRIZE_STATUS_OK   = 1; // 已中奖。
    const PRIZE_STATUS_NO   = 2; // 未中奖。

    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'gm_guess_record';

    /**
     * 中奖状态字典。
     * @var array
     */
    public static $prizeStatusDict = [
        self::PRIZE_STATUS_WAIT => '未开奖',
        self::PRIZE_STATUS_OK   => '已中奖',
        self::PRIZE_STATUS_NO   => '未中奖'
    ];
}
