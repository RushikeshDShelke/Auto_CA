<?php

namespace Cminds\Supplierfrontendproductuploader\Controller;

use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Model\Config\Source\Presentation\Visibility;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class AbstractController extends Action
{
    protected $resultPageFactory;
    protected $helper;
    protected $storeManager;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Data $helper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        if ($this->canAccess() === false) {
            if ($this->getHelper()->getCustomerSession()->isLoggedIn()) {
                return $this->force404();
            }

            return $this->redirectToLogin();
        }

        $params = $this->getRequest()->getParams();

        if (isset($params['submit']) && $params['submit'] === 'Export to CSV') {
            $this->_redirect('*/product/exportordered');
        }

        $this->_view->loadLayout();
        $this->renderBlocks();
        $this->_view->renderLayout();
    }

    protected function renderBlocks()
    {
        $headerVisibility = (int)$this->scopeConfig
            ->getValue('configuration/presentation/header_supplier_panel');
        if ($headerVisibility === Visibility::DO_NOT_SHOW) {
            $this->_view->getLayout()
                ->unsetElement('header.container')
                ->unsetElement('navigation.sections');
        }

        $footerVisibility = (int)$this->scopeConfig
            ->getValue('configuration/presentation/footer_supplier_panel');
        if ($footerVisibility === Visibility::DO_NOT_SHOW) {
            $this->_view->getLayout()
                ->unsetElement('footer')
                ->unsetElement('copyright');
        }
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function canAccess()
    {
        return $this->helper->canAccess(1);
    }

    protected function force404()
    {
        $this->_forward('defaultNoRoute');
    }

    protected function redirectToLogin()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->helper->getSupplierLoginPage());

        return $resultRedirect;
    }

    protected function getStoreManager()
    {
        return $this->storeManager;
    }
}
