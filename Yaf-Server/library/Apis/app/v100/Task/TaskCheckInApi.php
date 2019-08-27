<?php
/**
 * 任务打卡接口。
 * @author fingerQin
 * @date 2019-08-27
 */

namespace Apis\app\v100\Task;

use Apis\AbstractApi;
use Services\Task\Task;
use Services\User\Auth;

class TaskCheckInApi extends AbstractApi
{
    /**
     * 逻辑处理。
     *
     * @return void
     */
    protected function runService()
    {
        $token     = $this->getString('token', '');
        $userinfo  = Auth::checkAuth($token);
        $userid    = $userinfo['userid'];
        $taskId    = $this->getInt('task_id');
        $stepCount = $this->getInt('step_count');
        $longitude = $this->getFloat('longitude');
        $latitude  = $this->getFloat('latitude');
        $imageUrl  = $this->getString('imageUrl', '');
        Task::do($taskId, $userid, $stepCount, $longitude, $latitude, $imageUrl);
        $this->render(STATUS_SUCCESS, '签到成功');
    }
}