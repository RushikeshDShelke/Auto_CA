<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Product;

use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\View\Element\Template\Context;
use Cminds\Supplierfrontendproductuploader\Model\Product as CmindsProduct;
use Magento\Framework\View\Element\Template;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\ResourceConnection;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
// use Magento\InventorySalesApi\Api\StockResolverInterface;
// use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class Productlist extends Template
{
    protected $cmindsHelper;
    protected $product;
    protected $productFactory;
    protected $currencyHelper;
    protected $stockItem;
    protected $resource;
    protected $stockState;
    protected $productUploaderInventory;
    protected $stockId = null;

    public function __construct(
        Context $context,
        Data $helperCminds,
        Product $product,
        CurrencyHelper $coreHelper,
        StockItem $stockItem,
        ResourceConnection $resource,
        ProductFactory $productFactory,
        StockStateInterface $stockState,
        ProductUploaderInventory $productUploaderInventory
    ) {
        parent::__construct($context);

        $this->cmindsHelper = $helperCminds;
        $this->product = $product;
        $this->productFactory = $productFactory;
        $this->currencyHelper = $coreHelper;
        $this->stockItem = $stockItem;
        $this->resource = $resource;
        $this->stockState = $stockState;
        $this->productUploaderInventory = $productUploaderInventory;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getItems()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'supplier.product.pager'
            )->setAvailableLimit([20=>20])->setShowPerPage(true)->setCollection(
                $this->getItems()
            );
            $this->setChild('pager', $pager);
            $this->getItems()->load();
        }

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getItems()
    {
        $supplierId = $this->cmindsHelper->getSupplierId();

        $collection = $this->product->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('creator_id')
            ->addAttributeToSelect('frontendproduct_product_status')
            ->addAttributeToFilter(
                [
                    [
                        'attribute' => 'creator_id',
                        'eq' => $supplierId,
                    ],
                ]
            )
            ->setOrder('entity_id');

        $status = $this->_request->getParam('status');

        $name = $this->_request->getParam('name', null);

        $sku = $this->_request->getParam('sku', null);
        
        
        if ($name) {
            $collection->addFieldToFilter(
                [
                    ['attribute' => 'name', 'like' => '%' . $name . '%']
                    ]
                );
        }
        if ($sku) {
            $collection->addFieldToFilter(
                [
                    ['attribute' => 'sku', 'like' => '%' . $sku . '%']
                    ]
                );
        }

        switch ($status) {
            case 'pending':
                $collection->addAttributeToFilter(
                    [
                    [
                    'attribute' => 'frontendproduct_product_status',
                    'eq' => CmindsProduct::STATUS_PENDING,
                    ],
                    ]
                );
                break;
            case 'active':
                $collection->addAttributeToFilter(
                    [
                    [
                    'attribute' => 'frontendproduct_product_status',
                    'eq' => CmindsProduct::STATUS_APPROVED,
                    ],
                    ]
                );
                break;
            case 'inactive':
                $collection->addAttributeToFilter(
                    [
                    [
                    'attribute' => 'frontendproduct_product_status',
                    'eq' => CmindsProduct::STATUS_NONACTIVE,
                    ],
                    ]
                );
                break;
            case 'disapproved':
                $collection->addAttributeToFilter(
                    [
                    [
                    'attribute' => 'frontendproduct_product_status',
                    'eq' => CmindsProduct::STATUS_DISAPPROVED,
                    ],
                    ]
                );
                break;
            default:
                break;
        }

        $page = $this->_request->getParam('p', 1);
        $collection->setPageSize(20)->setCurPage($page);

        return $collection;
    }

    public function getStatusLabel($status)
    {
        switch ($status) {
            case CmindsProduct::STATUS_PENDING:
                return __('Pending');
                break;
            case CmindsProduct::STATUS_APPROVED:
                return __('Approved');
                break;
            case CmindsProduct::STATUS_DISAPPROVED:
                return __('Disapproved');
                break;
            case CmindsProduct::STATUS_NONACTIVE:
                return __('Not Active');
                break;
            default:
                return __('Unknown');
                break;
        }
    }

    public function getItemStatus()
    {
        return $this->_request->getParam('status', 'all');
    }

    public function getCmindsHelper()
    {
        return $this->cmindsHelper;
    }

    public function getItemsCount()
    {

        $items = $this->getItems()->getData();

        return count($items);
    }

    public function getProductModel()
    {
        $productCollection = $this->productFactory->create();

        return $productCollection;
    }

    public function getCurrencyHelper()
    {
        return $this->currencyHelper;
    }

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getQty($product_id)
    {
        $product = $this->productFactory->create()->load($product_id);
        $stock = 0;

        if (!$this->productUploaderInventory->inventoryIsSingleSourceMode()) {
            /** @var \Magento\InventorySalesApi\Api\StockResolverInterface */
            if (!$this->stockId) {
                $this->stockId = $this->productUploaderInventory
                    ->getStockResolverObject()
                    ->execute(
                        SalesChannelInterface::TYPE_WEBSITE,
                        $product->getStore()->getWebsite()->getCode()
                    )->getStockId();
            }

            /** @var \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface */
            if ($this->productUploaderInventory->getSourcesBySku($product->getSku())) {
                $stock = (int)$this->productUploaderInventory
                            ->getProductSalableQtyObject()
                            ->execute($product->getSku(), $this->stockId);
            }
        } else {
            $stock = $this->stockState->getStockQty(
                $product_id,
                $product->getStore()->getWebsiteId()
            );
        }
        return $stock;
    }
}
