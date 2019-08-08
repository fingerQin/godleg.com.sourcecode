<?php
/**
 * 取名首页数据接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Intitle;

class GameIntitleHomeApi extends AbstractApi
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
        $result = Intitle::hotWorld();
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}