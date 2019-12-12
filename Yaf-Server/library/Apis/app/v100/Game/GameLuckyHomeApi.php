<?php
/**
 * 大转盘首页接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Lucky;

class GameLuckyHomeApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $token  = $this->getString('token', '');
        $userid = Auth::getTokenUserId($token);
        $result = [
            'reward' => Lucky::rewards($isHasNo = true),
            'newest' => Lucky::getNewestRecords(10),
        ];
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}