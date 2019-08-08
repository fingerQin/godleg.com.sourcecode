<?php
/**
 * 幸运大转盘抽奖记录接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Lucky;

class GameLuckyRecordsApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $page      = $this->getInt('page', 1);
        $goodsType = $this->getInt('type', -1);
        $token     = $this->getString('token', '');
        $userinfo  = Auth::checkAuth($token);
        $result    = Lucky::records($userinfo['userid'], $goodsType, $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}