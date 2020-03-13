<?php
/**
 * 签到详情接口。
 * @author fingerQin
 * @date 2020-03-13
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Checkin;

class GameCheckInDetailApi extends AbstractApi
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
        $result   = Checkin::detail($userinfo['userid']);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}