<?php
/**
 * 金币消费记录表 Model。
 * @author fingerQin
 * @date 2019-08-07
 */

namespace Models;

use finger\Database\Db;

class GoldConsume extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_gold_consume';

    protected $primaryKey = 'id';

    /**
     * 消费类型。
     */
    const CONSUME_TYPE_ADD = 1; // 增加。
    const CONSUME_TYPE_CUT = 2; // 扣减。

    /**
     * 消费类型字典。
     *
     * @var array
     */
    public static $consumeTypeDict = [
        self::CONSUME_TYPE_ADD => '增加',
        self::CONSUME_TYPE_CUT => '扣减'
    ];

    /**
     * 消费定义。
     */
    const CONSUME_CODE_EXCHANGE  = 'exchange';           // 兑换商品扣减。
    const CONSUME_CODE_REGISTER  = 'register';           // 注册赠送。
    const CONSUME_CODE_EVERY_DAY = 'every_day';          // 每日赠送。
    const CONSUME_CODE_GUESS_ADD = 'question_guess_add'; // 题目竞猜中奖。
    const CONSUME_CODE_GUESS_CUT = 'question_guess_cut'; // 题目竞猜消费。
    const CONSUME_CODE_LUCKY_ADD = 'lucky_add';          // 幸运大转盘中奖。
    const CONSUME_CODE_LUCKY_CUT = 'lucky_cut';          // 幸运大转盘消费。
    const CONSUME_CODE_CHECK_IN  = 'check_in';           // 签到打卡。

    /**
     * 消费编码中标标签枚举。
     *
     * @var array
     */
    public static $consumeCodeLabels = [
        self::CONSUME_CODE_EXCHANGE  => '商品兑换',
        self::CONSUME_CODE_REGISTER  => '注册赠送',
        self::CONSUME_CODE_EVERY_DAY => '每日福利',
        self::CONSUME_CODE_GUESS_ADD => '题目竞猜中奖',
        self::CONSUME_CODE_GUESS_CUT => '题目竞猜消费',
        self::CONSUME_CODE_LUCKY_ADD => '大转盘中奖',
        self::CONSUME_CODE_LUCKY_CUT => '大转盘消费',
        self::CONSUME_CODE_CHECK_IN  => '签到赠送'
    ];
}