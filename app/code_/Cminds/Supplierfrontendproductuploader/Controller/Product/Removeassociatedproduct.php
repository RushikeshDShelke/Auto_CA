<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Removeassociatedproduct extends AbstractController
{
    private $product;
    private $logger;
    private $registry;
    private $productRepository;

    public function __construct(
        Context $context,
        Data $helper,
        CatalogProduct $product,
        ProductRepository $productRepository,
        LoggerInterface $loggerInterface,
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
        $this->productRepository = $productRepository;
        $this->logger = $loggerInterface;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }
        $this->registry->register('isSecureArea', true);

        $id = $this->getRequest()->getParam('product_id');
        $configurableId = $this->getRequest()->getParam('configurable_id');

        $manager = $this->getStoreManager();
        $manager->setCurrentStore(Store::DEFAULT_STORE_ID);

        try {
            if ($id === null) {
                throw new LocalizedException(__('No product id'));
            }

            if ($configurableId === null) {
                throw new LocalizedException(__('No configurable product id'));
            }

            $product = $this->product->load($id);

            $supplierId = $this->getHelper()->getSupplierId();
            if ($product->getData('creator_id') != $supplierId) {
                throw new LocalizedException(__('No product'));
            }

            $this->productRepository->deleteById($product->getSku());
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        $this->_redirect(
            'supplier/product/associatedproducts/id/' . $configurableId
        );
    }
}
