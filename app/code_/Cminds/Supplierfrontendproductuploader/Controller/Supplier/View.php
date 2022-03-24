<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Supplier;

use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cminds Supplierfrontendproductuploader admin supplier view controller.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 */
class View extends Action
{
    /**
     * Registry object.
     *
     * @var Registry
     */
    protected $_registry;

    /**
     * Session object.
     *
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Store manager object.
     *
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * Customer object.
     *
     * @var Customer
     */
    protected $_customer;

    /**
     * Context object.
     *
     * @var Context
     */
    protected $_context;

    /**
     * Scope config object.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Result page factory object.
     *
     * @var ResultFactory
     */
    protected $resultPageFactory;

    /**
     * Result redirect factory object.
     *
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Object constructor.
     *
     * @param Context               $context           Context object.
     * @param Data                  $helper            Data helper object.
     * @param Registry              $registry          Registry object.
     * @param CustomerSession       $customerSession   Session object.
     * @param StoreManagerInterface $storeManager      Store manager object.
     * @param Customer              $customer          Customer object.
     * @param ScopeConfigInterface  $scopeConfig       Scope config object.
     * @param PageFactory           $resultPageFactory Result page factory object.
     */
    public function __construct(
        Context $context,
        Data $helper,
        Registry $registry,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        Customer $customer,
        ScopeConfigInterface $scopeConfig,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->_registry = $registry;
        $this->_customerSession = $customerSession;
        $this->_storeManagerInterface = $storeManager;
        $this->_customer = $customer;
        $this->_context = $context;
        $this->scopeConfig = $scopeConfig;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Execute controller main logic.
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $id = $this->_request->getParam('id', false);
        $refererUrl = $this->_context->getRedirect()->getRefererUrl();

        if ($refererUrl) {
            $redirectUrl = $refererUrl;
        } else {
            $redirectUrl = $this->_storeManagerInterface->getStore()->getBaseUrl();
        }

        $value = $this->scopeConfig->getValue(
            'configuration_marketplace/configure/enable_supplier_pages'
        );

        if (!$value) {
            $this->messageManager->addErrorMessage('Profile is not enabled.');

            return $this->getResultRedirect($redirectUrl);
        }

        $customer = $this->_customer->load($id);
        if (!$customer->getSupplierProfileVisible()) {
            $this->messageManager->addErrorMessage('Profile was not created.');

            return $this->getResultRedirect($redirectUrl);
        }

        if (!$customer->getSupplierProfileApproved()) {
            $this->messageManager->addErrorMessage('Profile was not approved.');

            return $this->getResultRedirect($redirectUrl);
        }

        $this->_registry->register('customer', $customer);

        return $this->resultPageFactory->create();
    }

    /**
     * Return result redirect.
     *
     * @param string $redirectUrl Redirect url.
     *
     * @return Redirect
     */
    protected function getResultRedirect($redirectUrl)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($redirectUrl);

        return $resultRedirect;
    }
}
