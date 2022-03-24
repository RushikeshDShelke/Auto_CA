<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Editconfigurable extends AbstractController
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

        if (!$this->helper->canEditProducts()) {
            $resultRedirect = $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);

            $resultRedirect->setUrl('*/supplier/product/productlist/');

            return $resultRedirect;
        }

        $params = $this->_request->getParams();

        $id = $this->_request->getParam('id', null);

        if ($id === null) {
            throw new \Exception('No product id');
        }

        $p = $this->product->load($id);

        if ($p->getData('creator_id') != $this->getHelper()->getSupplierId()) {
            throw new \Exception('No product');
        }

        $this->registry->register('supplier_product_id', $id);
        $this->registry->register('is_configurable', false);
        $this->registry->register('cminds_configurable_request', $params);

        $this->_view->loadLayout();
        $this->renderBlocks();
        $this->_view->renderLayout();
    }
}
