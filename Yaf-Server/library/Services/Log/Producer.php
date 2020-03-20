<?php
/**
 * 系统日志收集器。
 *
 * @author fingerQin
 * @date 2020-03-20
 */

namespace Services\Log;

use finger\App;
use finger\Cache;
use finger\Core;

class Producer extends \Services\Log\AbstractBase
{
    /**
     * 监控上报数据推送。
     *
     * @param  array  $data  上报数据。
     *
     * @return void
     */
    public static function report(array $data)
    {
        // [1]
        if (empty($data)) {
            Core::exception(STATUS_SERVER_ERROR, '上报日志不能为空');
        }
        if (!isset($data['logcode'])) {
            Core::exception(STATUS_SERVER_ERROR, '上传日志的 logcode 不存在');
        }
        $code = strtolower($data['logcode']);
        if (!array_key_exists($code, self::$logCodeDict)) {
            Core::exception(STATUS_SERVER_ERROR, '日志位置 logcode 错误');
        }
        // [2] 写入 Redis 队列。
        $data['serial_no'] = self::serialNo();
        $redis  = Cache::getRedisClient();
        $status = $redis->lPush(self::LOG_QUEUE_KEY, json_encode($data, JSON_UNESCAPED_UNICODE));
        if ($status === false) {
            App::log($data, 'monitor', 'queue-error');
        }
    }

    /**
     * 返回流水序列号。
     *
     * @return string
     */
    protected static function serialNo()
    {
        $date   = date('YmdHi', TIMESTAMP);
        $key    = "logcode-{$date}";
        $redis  = Cache::getRedisClient();
        $intVal = $redis->incr($key);
        if ($intVal == 1) {
            $redis->expire($key, 120);
        }
        return $date . str_pad($intVal, 6, 0, STR_PAD_LEFT);
    }
}