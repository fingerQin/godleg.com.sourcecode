<?php
/**
 * 猜谜列表接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Riddle;

class GameRiddleListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $count  = 20;
        $score  = $this->getInt('score', 0);
        $page   = $this->getInt('page', 1);
        $token  = $this->getString('token', '');
        $userid = Auth::getTokenUserId($token);
        $result = Riddle::list($score, $page, $count);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}