<?php
/**
 * 打卡任务列表接口。
 * @author fingerQin
 * @date 2019-08-27
 */

namespace Apis\app\v100\Task;

use Apis\AbstractApi;
use Services\Task\Task;
use Services\User\Auth;

class TaskListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     *
     * @return void
     */
    protected function runService()
    {
        $token        = $this->getString('token', '');
        $userid       = Auth::getTokenUserId($token);
        $districtCode = $this->getString('district_code', '');
        $page         = $this->getInt('page', 1);
        $result       = Task::lists($districtCode, $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}