<?php
/**
 * 测前世游戏配置表 Model。
 * @author fingerQin
 * @date 2018-11-13
 */

namespace Models;

class GmPrelife extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'gm_prelife';

    protected $primaryKey = 'id';

    /**
     * 前世身份类别。
     */
    const TYPE_OFFICER  = 1; // 士。
    const TYPE_PEASANT  = 2; // 农。
    const TYPE_WORKERS  = 3; // 工。
    const TYPE_MERCHANT = 4; // 商。

    /**
     * 优先级。
     */
    const PRIORITY_HIGH   = 1; // 高。50% 概率。
    const PRIORITY_MIDDLE = 2; // 中。49% 概率。
    const PRIORITY_LOW    = 3; // 低。1%  概率。
}