<?php

namespace Meetanshi\Mobilelogin\Controller\Account;

use Magento\Framework\App\Action\Action;

/**
 * Class Register
 * @package Meetanshi\Mobilelogin\Controller\Account
 */
class Register extends Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
