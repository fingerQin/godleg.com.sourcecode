<?php
/**
 * 测今生游戏配置表 Model。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Models;

class GmThisLife extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'gm_this_life';

    protected $primaryKey = 'id';

    /**
     * 优先级。
     */
    const PRIORITY_HIGH   = 1; // 高。50% 概率。
    const PRIORITY_MIDDLE = 2; // 中。49% 概率。
    const PRIORITY_LOW    = 3; // 低。1%  概率。
}