<?php
/**
 * 谜题详情缓存清除接口。
 * @author fingerQin
 * @date 2018-11-19
 */

namespace Apis\admin\v100\Game;

use Apis\AbstractApi;
use Services\Game\Riddle;

class GameRiddleDetailClearCacheApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $this->isAllowAccessApi(0);
        $openID = $this->getString('openid');
        Riddle::clearDetailCache($openID);
        $this->render(STATUS_SUCCESS, '清除成功');
    }
}
