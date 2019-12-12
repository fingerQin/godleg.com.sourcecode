<?php
/**
 * 谜题管理。
 * @author fingerQin
 * @date 2018-11-14
 */

use finger\Url;
use finger\Paginator;
use Services\Game\Riddle;

class RiddleController extends \Common\controllers\Admin
{
    /**
     * 列表。
     */
    public function listAction()
    {
        $openid    = $this->getString('openid', '');
        $source    = $this->getInt('source', -1);
        $score     = $this->getInt('score', 0);
        $priority  = $this->getInt('priority', -1);
        $page      = $this->getInt('page', 1);
        $list      = Riddle::list($openid, $source, $score, $priority, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('openid', $openid);
        $this->assign('source', $source);
        $this->assign('score', $score);
        $this->assign('priority', $priority);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $priority    = $this->getInt('priority');
            $socre       = $this->getInt('score');
            $question    = $this->getString('question');
            $questionImg = $this->getString('question_img');
            $answer      = $this->getString('answer');
            $answerImg   = $this->getString('answer_img');
            Riddle::add($this->adminId, $priority, $socre, $question, $questionImg, $answer, $answerImg);
            $this->json(true, '添加成功');
        } else {
            $this->assign('files_domain_name', Url::getFilesDomainName());
        }
    }

    /**
     * 编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $id          = $this->getInt('id');
            $priority    = $this->getInt('priority');
            $socre       = $this->getInt('score');
            $question    = $this->getString('question');
            $questionImg = $this->getString('question_img');
            $answer      = $this->getString('answer');
            $answerImg   = $this->getString('answer_img');
            Riddle::edit($this->adminId, $id, $priority, $socre, $question, $questionImg, $answer, $answerImg);
            $this->json(true, '修改成功');
        } else {
            $id     = $this->getInt('id');
            $detail = Riddle::detail($id);
            $this->assign('detail', $detail);
            $this->assign('files_domain_name', Url::getFilesDomainName());
        }
    }

    /**
     * 删除。
     */
    public function deleteAction()
    {
        $id = $this->getInt('id');
        Riddle::delete($this->adminId, $id);
        $this->json(true, '删除成功');
    }

    /**
     * 重置缓存。
     */
    public function resetCacheAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            Riddle::resetCache();
            $this->json(true, '缓存重置成功');
        }
    }
}