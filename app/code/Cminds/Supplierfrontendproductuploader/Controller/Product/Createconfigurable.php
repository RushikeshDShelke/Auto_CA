<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Createconfigurable extends AbstractController
{
    protected $product;
    protected $registry;
    protected $adapter;

    public function __construct(
        Context $context,
        Data $helper,
        Product $product,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->product = $product;
        $this->registry = $registry;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }
        $params = $this->_request->getParams();

        if (!isset($params['attribute_set_id'])) {
            $this->_redirect('supplier/product/choosetype/');
            $this->messageManager->addErrorMessage(__('Missing Attribute Set ID'));

            return $this;
        }

        $this->registry->register('is_configurable', false);
        $this->registry->register('cminds_configurable_request', $params);

        $this->_view->loadLayout();
        $this->renderBlocks();
        $this->_view->renderLayout();
    }
}
