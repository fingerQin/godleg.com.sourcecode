<?php
/**
 * 测今生游戏配置管理。
 * @author fingerQin
 * @date 2018-11-14
 */

use finger\Paginator;
use Services\Game\ThisLife;

class ThislifeController extends \Common\controllers\Admin
{
    /**
     * 列表。
     */
    public function listAction()
    {
        $title     = $this->getString('title', '');
        $priority  = $this->getInt('priority', -1);
        $page      = $this->getInt('page', 1);
        $list      = ThisLife::list($title, $priority, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('title', $title);
        $this->assign('priority', $priority);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $priority = $this->getInt('priority');
            $socre    = $this->getInt('score');
            $title    = $this->getString('title');
            $intro    = $this->getString('intro');
            ThisLife::add($this->adminId, $priority, $socre, $title, $intro);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id       = $this->getInt('id');
            $priority = $this->getInt('priority');
            $socre    = $this->getInt('score');
            $title    = $this->getString('title');
            $intro    = $this->getString('intro');
            ThisLife::edit($this->adminId, $id, $priority, $socre, $title, $intro);
            $this->json(true, '修改成功');
        }
        $id     = $this->getInt('id');
        $detail = ThisLife::detail($id);
        $this->assign('detail', $detail);
    }

    /**
     * 删除。
     */
    public function deleteAction()
    {
        $id = $this->getInt('id');
        ThisLife::delete($this->adminId, $id);
        $this->json(true, '删除成功');
    }

    /**
     * 重置缓存。
     */
    public function resetCacheAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            ThisLife::resetCache();
            $this->json(true, '缓存重置成功');
        }
    }
}