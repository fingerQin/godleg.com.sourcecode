<?php
/**
 * 签到接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Checkin;

class GameCheckinApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $result   = Checkin::do($userinfo['userid']);
        $this->render(STATUS_SUCCESS, '签到成功', $result);
    }
}