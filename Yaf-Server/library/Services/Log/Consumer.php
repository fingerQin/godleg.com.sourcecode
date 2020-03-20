<?php
/**
 * 日志数据消费。
 * -- 采用多进程支持。实现数据消费的高吞吐。
 * @author fingerQin
 * @date 2020-03-20
 */

namespace Services\Log;

use finger\App;
use finger\Cache;
use finger\Database\Db;

class Consumer extends \finger\Thread\Thread
{
    /**
     * 运行真实的业务。
     * 
     * @param array $data 队列数据。
     *
     * @return bool true 消费成功、false - 消费失败。
     */
    protected function runService($data)
    {
        $className = ucfirst($data['code']);
        $className = "\\Services\\Log\\Sub\\{$className}";
        return $className::runService($data);
    }

    /**
     * 抽象的业务方法。
     * 
     * -- 注意
     * -- 1) 该方法只能由 PHP CLI 模式调用执行。
     * -- 2) 执行该方法之前不要有任何的 Redis 连接操作。
     * -- 3) 如果是单进程运行该方法可以忽略第 2 点。如果是多进程运行，必须保证第二点。因为，Redis 阻塞只能由多个 Redis 连接操作。
     * 
     * @param  int  $threadNum     进程数量。
     * @param  int  $num           子进程编号。
     * @param  int  $startTimeTsp  子进程启动时间戳。
     * 
     * @return void
     */
    public function run($threadNum, $num, $startTimeTsp)
    {
        // [1]
        $redis          = Cache::getRedisClient();
        $logQueueKey    = \Services\Log\AbstractBase::LOG_QUEUE_KEY;
        $logQueueIngKey = "{$logQueueKey}-{$num}-ing"; // 如果不加子进程编号，则多个子进程同时处理时会出现多进程同时消费情况。

        // [2] 无限循环处理消息队列的数据。
        // [2.1] 将当前正在处理的事件归恢复到事件池中。主要是预防进程重启导致正在处理的事件未正确处理。
        while ($redis->rPopLPush($logQueueIngKey, $logQueueKey)) {
            // 因为仅仅是利用 while 语句的循环特性,所以这里不需要实际业务代码。
        }

        // [2.2] 无限循环让进程一直处于常驻状态。
        try {
            $batResult = []; // 保存每批次入库的数据。
            while(true) {
                $strQueueVal = $redis->bRPopLPush($logQueueKey, $logQueueIngKey, 1);
                if ($strQueueVal) {
                    $arrQueueVal = json_decode($strQueueVal, true);
                    // [3] 调用具体的业务来处理这个消息。
                    try {
                        if (count($batResult) >= 100) {
                            $this->uploadLogToSuccess($batResult);
                        }
                        $this->runService($arrQueueVal);
                        $redis->lRem($logQueueIngKey, $strQueueVal, 1);
                        App::log($arrQueueVal, 'log', "{$arrQueueVal['place_code']}-success");
                    } catch (\Exception $e) {
                        $this->uploadLogToFail($arrQueueVal['place_code'], $strQueueVal, $e->getCode(), $e->getMessage());
                        $redis->lRem($logQueueIngKey, $strQueueVal, 1);
                    }
                } else {
                    Db::ping();
                    Cache::ping();
                }
                $this->isExit($startTimeTsp);
            }
        } catch (\Exception $e) {
            $code = $arrQueueVal['place_code'] ?? '';
            $this->exceptionExit($code, $strQueueVal, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 批量上传日志。
     *
     * @param  array  $batResult  日志。
     *
     * @return void
     */
    protected function uploadLogToSuccess($batResult)
    {

    }

    /**
     * 队列消费失败。
     * 
     * @param  string  $code      日志上传编码。
     * @param  string  $data      队列取出来的数据。
     * @param  int     $errCode   错误码。
     * @param  string  $errMsg    错误消息。
     * 
     * @return void
     */
    protected function uploadLogToFail($code, $data, $errCode, $errMsg)
    {
        $log = [
            'value' => $data,
            'code'  => $errCode,
            'msg'   => $errMsg
        ];
        App::log($log, 'log', "{$code}-fail");
        unset($MonitorModel, $updata, $log);
    }

    /**
     * 异常退出。
     *
     * @param  string  $code     日志上报编码。
     * @param  string  $data     队列取出来的数据。
     * @param  int     $errCode  错误码。
     * @param  string  $errMsg   错误信息。
     *
     * @return void
     */
    protected function exceptionExit($code, $data, $errCode, $errMsg)
    {
        $log = [
            'value' => $data,
            'code'  => $errCode,
            'msg'   => $errMsg
        ];
        App::log($log, 'log', "{$code}-error");
        $datetime = date('Y-m-d H:i:s', time());
        exit("ErrorTime:{$datetime}\nErrorMsg:{$errMsg}\n");
    }
}