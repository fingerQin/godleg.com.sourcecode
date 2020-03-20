<?php
/**
 * 系统日志收集接口。
 * 
 * @author fingerQin
 * @date 2020-03-20
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\Log\Producer;
use Services\User\Auth;

class SystemLogApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token   = $this->getString('token', '');
        $logData = $this->params;
        $userid  = Auth::getTokenUserId($token);
        $logData['_userid'] = $userid;
        unset($logData['oriJson'], $logData['sign'], $logData['token']);
        $result = Producer::report($logData);
        $this->render(STATUS_SUCCESS, 'success', ['list' => $result]);
    }
}