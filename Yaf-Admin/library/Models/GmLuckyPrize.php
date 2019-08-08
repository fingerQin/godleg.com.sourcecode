<?php
/**
 * 抽奖中奖记录表。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Models;

class GmLuckyPrize extends AbstractBase
{
    /**
     * 奖品发送状态。
     */
    const SEND_STATUS_YES = 1; // 已发送。 
    const SEND_STATUS_NO  = 0; // 未发送。

    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'gm_lucky_prize';
}
