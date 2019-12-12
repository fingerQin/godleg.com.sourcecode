<?php
/**
 * 名字库管理。
 * @author fingerQin
 * @date 2018-11-14
 */

use finger\Paginator;
use Services\Game\Intitle;

class NameController extends \Common\controllers\Admin
{
    /**
     * 列表。
     */
    public function listAction()
    {
        $name      = $this->getString('name', '');
        $type      = $this->getString('type', -1);
        $sex       = $this->getString('sex', '');
        $page      = $this->getInt('page', 1);
        $list      = Intitle::list($name, $type, $sex, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('name', $name);
        $this->assign('type', $type);
        $this->assign('sex', $sex);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $name = $this->getString('name');
            $type = $this->getInt('type');
            $sex  = $this->getString('sex');
            $expl = $this->getString('expl');
            Intitle::add($this->adminId, $name, $type, $sex, $expl);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id   = $this->getInt('id');
            $name = $this->getString('name');
            $type = $this->getInt('type');
            $sex  = $this->getString('sex');
            $expl = $this->getString('expl');
            Intitle::edit($this->adminId, $id, $name, $type, $sex, $expl);
            $this->json(true, '修改成功');
        } else {
            $id     = $this->getInt('id');
            $detail = Intitle::detail($id);
            $this->assign('detail', $detail);
        }
    }

    /**
     * 删除。
     */
    public function deleteAction()
    {
        $id = $this->getInt('id');
        Intitle::delete($this->adminId, $id);
        $this->json(true, '删除成功');
    }

    /**
     * 重置缓存。
     */
    public function resetCacheAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            Intitle::resetCache();
            $this->json(true, '缓存重置成功');
        }
    }
}