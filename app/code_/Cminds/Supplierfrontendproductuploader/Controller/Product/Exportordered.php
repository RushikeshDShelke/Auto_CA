<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Sales\Model\Order\Item as SalesOrderItem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;

class Exportordered extends AbstractController
{
    /**
     * Marketplace helper instance.
     *
     * @var Data
     */
    private $marketplaceHelper;

    /**
     * ResourceConnection instance.
     *
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Raw factory instance.
     *
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * EntityAttribute resource model instance.
     *
     * @var Attribute
     */
    private $entityAttribute;

    /**
     * Helper/Data model instance.
     *
     * @var Helper
     */
    protected $supplierHelper;
    
    /**
     * OrderItem model instance.
     *
     * @var OrderItem
     */
    protected $salesOrderItem;
  
    /**
     * Product factory model instance.
     *
     * @var ProductFactory
     */
    protected $productFactory;
    
    /**
     * Currency/Helper/Data model instance.
     *
     * @var Data
     */
    protected $currencyHelper;
    
    public function __construct(
        Context $context,
        Helper $helper,
        Data $cmindsHelper,
        ResourceConnection $resourceConnection,
        RawFactory $rawFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Attribute $entityAttribute,
        SalesOrderItem $salesOrderItem,
        ProductFactory $productFactory,
        CurrencyHelper $currencyHelper
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->marketplaceHelper = $cmindsHelper;
        $this->resourceConnection = $resourceConnection;
        $this->resultRawFactory = $rawFactory;
        $this->entityAttribute = $entityAttribute;
        $this->supplierHelper = $helper;
        $this->salesOrderItem = $salesOrderItem;
        $this->productFactory = $productFactory;
        $this->currencyHelper = $currencyHelper;
    }

    /**
     * Prepare CSV file with bestsellers.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $collection = $this->getCollection();
        $productCsv = [];

        $i = 1;
        foreach ($collection as $item) {
            $product = $this->getLoadedProduct($item->getProductId());
            $productCsv[] = [
                'No.' => $i++,
                'Product Name' => $product->getName(),
                'Product SKU' => $product->getSKU(),
                'Quantity Sold' => intval($item->getItemCount()),
                'Subtotal' => $this->currencyHelper->currency($item->getRowTotal(),true,false),
            ];
        }

        $this->marketplaceHelper
            ->prepareCsvHeaders('ordered_items_export_' . date('Y-m-d') . '.csv');

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->marketplaceHelper->array2Csv($productCsv));

        return $resultRaw;
    }

    /**
     * Get bestseller collection with filters.
     *
     * @return BestsellersCollection
     */
    private function getCollection()
    {
        return $this->prepareCollection();
    }

    /**
     * Add filters to bestseller collection.
     *
     * @return BestsellersCollection
     */
    private function prepareCollection()
    {
        $eavAttribute = $this->entityAttribute;
        $supplier_id = $this->supplierHelper->getSupplierId();
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table = "catalog_product_entity_int";
        $tableName = $this->resourceConnection->getTableName($table);
        $orderTable = $this->resourceConnection->getTableName('sales_order');
        $collection = $this->salesOrderItem->getCollection();
        
        $collection->addExpressionFieldToSelect(
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

    /**
     * Get value from post/get array by key.
     *
     * @param $key
     *
     * @return mixed
     */
    private function getFilter($key)
    {
        return $this->_request->getParam($key);
    }

    /**
     * Get logged supplier ID.
     *
     * @return bool|mixed
     */
    private function getSupplierId()
    {
        return $this->marketplaceHelper->getSupplierId();
    }
    
    /**
     * Get logged product from id.
     *
     * @return bool|mixed
     */
    public function getLoadedProduct($id)
    {
        $productCollection = $this->productFactory->create()->load($id);

        return $productCollection;
    }
}
