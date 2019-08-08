<?php
/**
 * 竞猜投注。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Guess;

class GameGuessDoApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $gold        = $this->getInt('gold');
        $option      = $this->getString('option');
        $guessId     = $this->getInt('guessid');
        $token       = $this->getString('token', '');
        $userinfo    = Auth::checkAuth($token);
        $residueGold = Guess::do($userinfo['userid'], $guessId, $option, $gold);
        $this->render(STATUS_SUCCESS, '竞猜成功', ['gold' => $residueGold]);
    }
}