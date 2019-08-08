<?php
/**
 * 大转盘抽奖活动管理。
 * @author fingerQin
 * @date 2018-02-28
 */

use finger\Paginator;
use Utils\YCore;
use Utils\YUrl;
use Services\Game\Lucky;

class LuckyController extends \Common\controllers\Admin
{
    /**
     * 活动列表。
     */
    public function listAction()
    {
        $list = Lucky::getGoodsList();
        $filesDomainName = YUrl::getFilesDomainName();
        $this->assign('files_domain_name', $filesDomainName);
        $this->assign('list', $list);
    }

    /**
     * 设置活动奖品。
     */
    public function setAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $goods = $this->getArray('goods');
            Lucky::setGoods($this->adminId, $goods);
            $this->json(true, '保存成功');
        }
    }

    /**
     * 抽奖记录列表。
     */
    public function recordAction()
    {
        $mobile    = $this->getString('mobile', '');
        $goodsName = $this->getString('goods_name', '');
        $goodsType = $this->getString('goods_type', '');
        $page      = $this->getInt('page', 1);
        $list      = Lucky::records($mobile, $goodsName, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('list', $list['list']);
        $this->assign('mobile', $mobile);
        $this->assign('goods_name', $goodsName);
    }
}