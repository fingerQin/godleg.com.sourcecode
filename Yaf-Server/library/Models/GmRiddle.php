<?php
/**
 * 猜猜猜题库表。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Models;

class GmRiddle extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'gm_riddle';

    protected $primaryKey = 'id';

    /**
     * 优先级。
     */
    const PRIORITY_HIGH   = 1; // 高。50% 概率。
    const PRIORITY_MIDDLE = 2; // 中。49% 概率。
    const PRIORITY_LOW    = 3; // 低。1%  概率。

    /**
     * 来源类型。
     */
    const SOURCE_SYSTEM = 0; // 系统创建。
    const SOURCE_USER   = 1; // 用户创建(VIP)。
}