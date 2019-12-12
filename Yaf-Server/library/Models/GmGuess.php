<?php
/**
 * 竞猜活动表。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Models;

class GmGuess extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'gm_guess';

    const SEND_STATUS_NO      = 0; // 待发送。
    const SEND_STATUS_ING     = 1; // 发送中。
    const SEND_STATUS_FINISH  = 2; // 发送完成。
}
