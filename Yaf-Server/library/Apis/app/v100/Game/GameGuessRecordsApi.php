<?php
/**
 * 竞猜投注记录接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Guess;

class GameGuessRecordsApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $page     = $this->getInt('page', 1);
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $result   = Guess::records($userinfo['userid'], $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}