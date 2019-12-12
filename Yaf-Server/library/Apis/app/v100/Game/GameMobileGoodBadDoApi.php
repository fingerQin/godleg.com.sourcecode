<?php
/**
 * 手机号测吉凶接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\MobileLuckyBad;

class GameMobileGoodBadDoApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $mobile = $this->getString('mobile', '');
        $token  = $this->getString('token', '');
        $userid = Auth::getTokenUserId($token);
        $result = MobileLuckyBad::do($mobile);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}