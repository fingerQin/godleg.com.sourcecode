<?php
/**
 * 测前世接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\PreLife;

class GamePrelifeDoApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $name   = $this->getString('name', '');
        $token  = $this->getString('token', '');
        $userid = Auth::getTokenUserId($token);
        $result = PreLife::do($name);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}