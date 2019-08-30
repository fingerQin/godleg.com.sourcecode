<?php
/**
 * 竞猜活动管理。
 * @author fingerQin
 * @date 2018-08-28
 */

use Utils\YUrl;
use finger\Paginator;
use Services\Game\Guess;

class GuessController extends \Common\controllers\Admin
{
    /**
     * 活动列表。
     */
    public function listAction()
    {
        $title     = $this->getString('title', '');
        $startTime = $this->getString('start_time', '');
        $endTime   = $this->getString('end_time', '');
        $isOpen    = $this->getInt('is_open', -1);
        $page      = $this->getInt('page', 1);
        $list      = Guess::list($title, $startTime, $endTime, $isOpen, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('list', $list['list']);
        $this->assign('title', $title);
        $this->assign('start_time', $startTime);
        $this->assign('end_time', $endTime);
        $this->assign('is_open', $isOpen);
    }

    /**
     * 活动添加。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $title       = $this->getString('title');
            $imageUrl    = $this->getString('image_url');
            $optionsData = $this->getArray('options_data');
            $deadline    = $this->getString('deadline');
            $openResult  = $this->getString('open_result', '');
            Guess::add($this->adminId, $title, $imageUrl, $optionsData, $deadline, $openResult);
            $this->json(true, '添加成功');
        } else {
            $fileDomain = YUrl::getFilesDomainName();
            $this->assign('files_domain_name', $fileDomain);
            $this->assign('options', Guess::$options);
        }
    }

    /**
     * 添加编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $guessId     = $this->getInt('guessid');
            $title       = $this->getString('title');
            $imageUrl    = $this->getString('image_url');
            $optionsData = $this->getArray('options_data');
            $isOpen      = $this->getInt('is_open');
            $deadline    = $this->getString('deadline');
            $openResult  = $this->getString('open_result', '');
            Guess::edit($this->adminId, $guessId, $title, $imageUrl, $isOpen, $optionsData, $deadline, $openResult);
            $this->json(true, '修改成功');
        } else {
            $guessId    = $this->getInt('guessid');
            $detail     = Guess::detail($guessId);
            $fileDomain = YUrl::getFilesDomainName();
            $this->assign('files_domain_name', $fileDomain);
            $this->assign('detail', $detail);
            $this->assign('options', Guess::$options);
        }
    }

    /**
     * 活动删除。
     */
    public function deleteAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $guessId = $this->getInt('guessid');
            Guess::delete($this->adminId, $guessId);
            $this->json(true, '删除成功');
        }
    }

    /**
     * 参与记录。
     */
    public function recordAction()
    {
        $mobile      = $this->getString('mobile', '');
        $prizeStatus = $this->getInt('prize_status', -1);
        $page        = $this->getInt('page', 1);
        $guessId     = $this->getInt('guessid');
        $list        = Guess::records($guessId, $mobile, $prizeStatus, $page, 20);
        $paginator   = new Paginator($list['total'], 20);
        $pageHtml    = $paginator->backendPageShow();
        $this->assign('pageHtml', $pageHtml);
        $this->assign('list', $list['list']);
        $this->assign('guessid', $guessId);
        $this->assign('mobile', $mobile);
        $this->assign('prize_status', $prizeStatus);
    }
}