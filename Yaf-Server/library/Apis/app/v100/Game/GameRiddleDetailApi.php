<?php
/**
 * 猜谜详情接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Game\Riddle;

class GameRiddleDetailApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $quesId = $this->getString('ques_id');
        $token  = $this->getString('token', '');
        $userid = Auth::getTokenUserId($token);
        $result = Riddle::detail($quesId);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}