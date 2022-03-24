<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Model\Product\Configurable as CmindsConfigurableModel;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store as Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Changeassociatedstatus extends AbstractController
{
    protected $product;
    protected $cmindsConfigurableModel;
    protected $logger;

    public function __construct(
        Context $context,
        Data $helper,
        CatalogProduct $product,
        CmindsConfigurableModel $cmindsConfigurableModel,
        LoggerInterface $logger,
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
        $this->cmindsConfigurableModel = $cmindsConfigurableModel;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $id = $this->_request->getParam('product_id', null);
        $configurableId = $this->_request->getParam('configurable_id', null);

        try {
            if ($id === null) {
                throw new \Exception(__('No product id'));
            }

            if ($configurableId === null) {
                throw new \Exception(__('No product id'));
            }

            $configurableProduct = $this->product->load($configurableId);
            $product = $this->product->load($id);

            $configurableModel = $this->cmindsConfigurableModel;
            $configurableModel->setProduct($configurableProduct);
            $configurableProductsData = $configurableModel
                ->getConfigurableProductValues();

            $additionalPrice = 0;

            if ($this->getRequest()->getParam('status') === 'true') {
                $configurableProductsData[$product->getId()][] = [
                    'is_percent' => '0',
                ];

                $configurableProduct->setCanSaveConfigurableAttributes(true);
                $product->setPrice(
                    $configurableProduct->getPrice() + $additionalPrice
                );
            } else {
                if (isset($configurableProductsData[$product->getId()])) {
                    unset($configurableProductsData[$product->getId()]);
                }
            }
            $this->getStoreManager()->setCurrentStore(Store::DEFAULT_STORE_ID);

            $configurableProduct->setConfigurableProductsData(
                $configurableProductsData
            );
            $configurableProduct->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
