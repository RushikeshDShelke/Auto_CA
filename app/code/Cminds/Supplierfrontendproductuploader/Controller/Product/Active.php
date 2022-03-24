<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Model\Product as CmindsProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockRegistryInterface as StockRegistry;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Active extends AbstractController
{
    protected $product;
    protected $registry;
    protected $stockRegistry;

    public function __construct(
        Context $context,
        Data $helper,
        Product $product,
        Registry $registry,
        StockRegistry $stockRegistry,
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
        $this->stockRegistry = $stockRegistry;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $id = $this->_request->getParam('id', null);

        if ($id == null) {
            throw new \Exception('No product id');
        }

        $p = $this->product->load($id);

        if ($p->getData('creator_id') != $this->getHelper()->getSupplierId()) {
            throw new \Exception('No product');
        }

        $this->getStoreManager()->setCurrentStore(Store::DEFAULT_STORE_ID);

        $inArray = in_array(
            $p->getData('frontendproduct_product_status'),
            [
                CmindsProduct::STATUS_PENDING,
                CmindsProduct::STATUS_DISAPPROVED,
            ]
        );
        if (!$inArray) {
            $p->setSupplierActivedProduct(1);
            $p->setVisibility(Visibility::VISIBILITY_BOTH);
            $p->getResource()->saveAttribute($p, 'supplier_actived_product');

            $p->setFrontendproductProductStatus(CmindsProduct::STATUS_APPROVED);
            $p->getResource()->saveAttribute(
                $p,
                'frontendproduct_product_status'
            );

            $stockItem = $this->stockRegistry->getStockItem($p->getId());
            $stockItem
                ->setIsInStock(true)
                ->save();

            $p->save();
        }
        $this->_redirect('supplier/product/productlist/');
    }
}
