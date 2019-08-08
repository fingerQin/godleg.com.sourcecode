<?php
/**
 * 竞猜题目列表。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Guess;

class GameGuessQuestionsApi extends AbstractApi
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
        $userinfo = Auth::getTokenUserId($token);
        $result   = Guess::list($page, $count = 20);
        $this->render(STATUS_SUCCESS, '竞猜成功', $result);
    }
}