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

class Associatedproducts extends AbstractController
{
    protected $product;
    protected $registry;

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

        $this->_view->loadLayout();

        $id = $this->getRequest()->getParam('id');
        $product = $this->product->load($id);

        if (!$product->getId()) {
            throw new \Exception(__('Super Product Not Found'));
        }

        $this->registry->register('product_object_configurable_id', $id);
        $this->renderBlocks();

        $this->_view->renderLayout();
    }
}
