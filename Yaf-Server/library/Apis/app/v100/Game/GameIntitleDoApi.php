<?php
/**
 * 取名接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Intitle;

class GameIntitleDoApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $familyName  = $this->getString('family_name', '');
        $sex         = $this->getString('sex', '');
        $token       = $this->getString('token', '');
        $userid      = Auth::getTokenUserId($token);
        $result      = Intitle::do($familyName, $sex);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}