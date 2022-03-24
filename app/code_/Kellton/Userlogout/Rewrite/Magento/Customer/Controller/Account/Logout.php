<?php


namespace Kellton\Userlogout\Rewrite\Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Customer\Controller\AbstractAccount;
use Cminds\Supplierfrontendproductuploader\Helper\Data as supplierhelper;

/**
 * Class Logout
 *
 * @package Kellton\Userlogout\Rewrite\Magento\Customer\Controller\Account
 */
class Logout extends \Magento\Customer\Controller\Account\Logout
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    protected $supplierhelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        supplierhelper $supplierhelper
    ) {
        $this->session = $customerSession;
        $this->_supplierhelper = $supplierhelper;

        parent::__construct($context,$customerSession);
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Custom Customer logout action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $flagIsSupplier = false;
        $lastCustomerId = $this->session->getId();
        if ($this->_supplierhelper->isSupplier($lastCustomerId)) {
            $flagIsSupplier = true;
        }
        $this->session->logout()->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if($flagIsSupplier){
            $resultRedirect->setPath('supplier/account/login');
            $flagIsSupplier = false;
        } else{
            $resultRedirect->setPath('*/*/logoutSuccess');
        }
        return $resultRedirect;
    }

}

