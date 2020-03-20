<?php
/**
 * 系统日志收集器基类。
 * @author fingerQin
 * @date 2020-03-20
 */

namespace Services\Log;

abstract class AbstractBase extends \Services\AbstractBase
{
    /**
     * 监控队列 KEY。
     */
    const LOG_QUEUE_KEY = 'logs-queue';

    /**
     * 日志编码字典。
     *
     * @var array
     */
    protected static $logCodeDict = [
        'LOGIN'         => '登录日志 CODE',
        'REGISTER'      => '注册日志 CODE',
        'GOODS_DETAIL'  => '商品详情日志 CODE'
    ];
}