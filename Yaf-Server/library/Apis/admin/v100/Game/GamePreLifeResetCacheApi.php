<?php
/**
 * 测前世游戏缓存清除接口。
 * @author fingerQin
 * @date 2018-11-19
 */

namespace Apis\admin\v100\Game;

use Apis\AbstractApi;
use Services\Game\PreLife;

class GamePreLifeResetCacheApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $this->isAllowAccessApi(0);
        PreLife::resetCache();
        $this->render(STATUS_SUCCESS, '重置成功');
    }
}
