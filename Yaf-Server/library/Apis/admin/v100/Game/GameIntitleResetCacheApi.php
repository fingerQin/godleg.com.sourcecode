<?php
/**
 * 取名游戏缓存重置接口。
 * @author fingerQin
 * @date 2018-11-19
 */

namespace Apis\admin\v100\Game;

use Apis\AbstractApi;
use Services\Game\Intitle;

class GameIntitleResetCacheApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $this->isAllowAccessApi(0);
        Intitle::resetCache();
        $this->render(STATUS_SUCCESS, '重置成功');
    }
}
