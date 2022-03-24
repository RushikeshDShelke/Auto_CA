<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Account;

use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Login extends \Magento\Customer\Controller\Account\Login
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var
     */
    protected $_helper;

    /**
     * @param Context     $context
     * @param Session     $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->_helper = $helper;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct(
            $context,
            $customerSession,
            $resultPageFactory
        );
    }

    /**
     * Customer login form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->_helper->canLogin()) {
            $this->_forward('defaultNoRoute');

            return;
        }
        if ($this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('supplier');

            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setHeader('Login-Required', 'true');

        return $resultPage;
    }
}
