<?php
/**
 * 幸运大转盘抽最新中奖记录接口。
 * @author fingerQin
 * @date 2019-08-08
 * @version 1.0.0
 */

namespace Apis\app\v100\Game;

use Apis\AbstractApi;
use Services\Game\Lucky;

class GameLuckyNewestApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $result = Lucky::getNewestRecords(20);
        $this->render(STATUS_SUCCESS, 'success', ['list' => $result]);
    }
}