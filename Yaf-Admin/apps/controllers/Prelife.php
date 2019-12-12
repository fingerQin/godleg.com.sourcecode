<?php
/**
 * 测前世游戏配置管理。
 * @author fingerQin
 * @date 2018-11-14
 */

use finger\Paginator;
use Services\Game\PreLife;

class PrelifeController extends \Common\controllers\Admin
{
    /**
     * 列表。
     */
    public function listAction()
    {
        $title     = $this->getString('title', '');
        $priority  = $this->getInt('priority', -1);
        $type      = $this->getInt('type', -1);
        $page      = $this->getInt('page', 1);
        $list      = PreLife::list($title, $priority, $type, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('title', $title);
        $this->assign('priority', $priority);
        $this->assign('type', $type);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $priority = $this->getInt('priority');
            $title    = $this->getString('title');
            $type     = $this->getInt('type');
            $intro    = $this->getString('intro');
            PreLife::add($this->adminId, $priority, $title, $type, $intro);
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
            $title    = $this->getString('title');
            $type     = $this->getInt('type');
            $intro    = $this->getString('intro');
            PreLife::edit($this->adminId, $id, $priority, $title, $type, $intro);
            $this->json(true, '修改成功');
        } else {
            $id     = $this->getInt('id');
            $detail = PreLife::detail($id);
            $this->assign('detail', $detail);
        }
    }

    /**
     * 删除。
     */
    public function deleteAction()
    {
        $id = $this->getInt('id');
        PreLife::delete($this->adminId, $id);
        $this->json(true, '删除成功');
    }

    /**
     * 重置缓存。
     */
    public function resetCacheAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            PreLife::resetCache();
            $this->json(true, '缓存重置成功');
        }
    }
}