<?php

namespace Meetanshi\Mobilelogin\Controller\Account;

use Magento\Customer\Controller\AbstractAccount;

/**
 * Class Forgotpassword
 * @package Meetanshi\Mobilelogin\Controller\Account
 */
class Forgotpassword extends AbstractAccount
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
