<?php
/**
 * 任务打卡记录接口。
 * @author fingerQin
 * @date 2019-08-27
 */

namespace Apis\app\v100\Task;

use Apis\AbstractApi;
use Services\Task\Task;
use Services\User\Auth;

class TaskRecordsApi extends AbstractApi
{
    /**
     * 逻辑处理。
     *
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $userid   = $userinfo['userid'];
        $page     = $this->getInt('page', 1);
        $result   = Task::record($userid, $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}