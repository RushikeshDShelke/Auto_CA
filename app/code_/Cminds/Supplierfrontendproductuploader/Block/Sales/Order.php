<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Sales;

use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Sales\Model\Order\Item as SalesOrderItem;
use Magento\Framework\View\Element\Template as Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Order extends Template
{
    protected $productFactory;
    protected $objectManager;
    protected $supplierHelper;
    protected $coreResource;
    protected $salesOrderItem;
    protected $imageHelper;
    protected $currencyHelper;
    protected $entityAttribute;

    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        ObjectManagerInterface $objectManager,
        SupplierHelper $supplierHelper,
        ResourceConnection $resourceConnection,
        SalesOrderItem $salesOrderItem,
        Image $image,
        CurrencyHelper $currencyHelper,
        Attribute $entityAttribute
    ) {
        parent::__construct($context);

        $this->productFactory = $productFactory;
        $this->objectManager = $objectManager;
        $this->supplierHelper = $supplierHelper;
        $this->coreResource = $resourceConnection;
        $this->salesOrderItem = $salesOrderItem;
        $this->imageHelper = $image;
        $this->currencyHelper = $currencyHelper;
        $this->entityAttribute = $entityAttribute;
    }

    public function getItems()
    {
        $collection = $this->prepareCollection();

        return $collection;
    }

    private function prepareCollection()
    {
        $eavAttribute = $this->entityAttribute;
        $supplier_id = $this->supplierHelper->getSupplierId();
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table = "catalog_product_entity_int";
        $tableName = $this->coreResource->getTableName($table);
        $orderTable = $this->coreResource->getTableName('sales_order');
        $collection = $this->salesOrderItem->getCollection();
        $page = $this->_request->getParam('p', 1);

        $collection->setPageSize(10)->setCurPage($page)->addExpressionFieldToSelect(
            'item_count',
            'SUM({{qty_ordered}})',
            'qty_ordered'
        );

        $collection->getSelect()
            ->joinInner(
                ['o' => $orderTable],
                'o.entity_id = main_table.order_id',
                []
            )
            ->joinInner(
                ['e' => $tableName],
                "e.{$this->supplierHelper->getRowIdentifier()} = main_table.product_id AND e.attribute_id = $code",
                []
            )
            ->where('main_table.parent_item_id is null')
            ->where('e.value = ?', $supplier_id)
            ->where('o.state != ?', 'canceled');

        if ($this->getFilter('from') && strtotime($this->getFilter('from'))) {
            $datetime = new \DateTime($this->getFilter('from'));
            $collection->getSelect()->where(
                'main_table.created_at >= ?',
                $datetime->format('Y-m-d 00:00:00')
            );
        }
        if ($this->getFilter('to') && strtotime($this->getFilter('to'))) {
            $datetime = new \DateTime($this->getFilter('to'));
            $collection->getSelect()->where(
                'main_table.created_at <= ?',
                $datetime->format('Y-m-d 23:59:59')
            );
        }

        $collection->getSelect()->group('main_table.product_id');

        return $collection;
    }

    private function getFilter($key)
    {
        return $this->_request->getParam($key);
    }

    public function getParams()
    {
        return $this->_request;
    }

    public function getLoadedProduct($id)
    {
        $productCollection = $this->productFactory->create()->load($id);

        return $productCollection;
    }

    public function getProductImageUrl($item)
    {
        $imageUrl = $this->imageHelper->init($item, 'product_thumbnail_image')->getUrl();

        return $imageUrl;
    }

    public function getCurrencyHelper()
    {
        return $this->currencyHelper;
    }
}
