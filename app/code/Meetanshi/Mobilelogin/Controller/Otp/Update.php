<?php

namespace Meetanshi\Mobilelogin\Controller\Otp;

use Magento\Framework\App\Action\Action;

/**
 * Class Update
 * @package Meetanshi\Mobilelogin\Controller\Otp
 */
class Update extends Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Mobile Number'));
        $this->_view->renderLayout();
    }
}
