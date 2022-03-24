<?php

namespace Cminds\Supplierfrontendproductuploader\Block;

use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

class Dashboard extends Template
{
    protected $_product;
    protected $currentCustomer;
    protected $_eventFactory;
    protected $resourceConnection;
    protected $_attribute;
    protected $_cmindsHelper;
    protected $_order;
    protected $_currencyHelper;

    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        ResourceConnection $resourceConnection,
        Attribute $attribute,
        CmindsHelper $cmindsHelper,
        Order $order,
        CurrencyHelper $coreHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->currentCustomer = $currentCustomer;
        $this->resourceConnection = $resourceConnection;
        $this->_attribute = $attribute;
        $this->_cmindsHelper = $cmindsHelper;
        $this->_order = $order;
        $this->_currencyHelper = $coreHelper;
    }

    protected function _prepareCollection()
    {
        $eavAttribute = $this->_attribute;
        $supplier_id = $this->_cmindsHelper->getSupplierId();
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table = "catalog_product_entity_int";
        $tableName = $this->resourceConnection->getTableName($table);

        $orderItemTable = $this->resourceConnection
            ->getTableName('sales_order_item');

        $collection = $this->_order->getCollection();
        $collection->getSelect()
            ->joinInner(
                ['i' => $orderItemTable],
                'i.order_id = main_table.entity_id',
                []
            )
            ->joinInner(
                ['e' => $tableName],
                "e.{$this->_cmindsHelper->getRowIdentifier()} = i.product_id AND e.attribute_id = $code",
                []
            )
            ->where('i.parent_item_id is null')
            ->where('e.value = ?', $supplier_id)
            ->where('main_table.state = \'complete\'');

        return $collection;
    }

    public function getSupplierSaleAmount()
    {
        $collection = $this->_prepareCollection();

        $collection->addExpressionFieldToSelect(
            'sale_amount',
            'SUM(i.price*i.qty_ordered)',
            'i.price'
        );

        return $collection->getFirstItem()->getData('sale_amount');
    }

    public function getSupplierSaleAvg()
    {
        $collection = $this->_prepareCollection();

        $collection->addExpressionFieldToSelect(
            'sale_avg',
            'AVG(i.price*i.qty_ordered)',
            'i.price'
        );

        return $collection->getFirstItem()->getData('sale_avg');
    }

    public function getSupplierSaleCount()
    {
        $collection = $this->_prepareCollection();

        $collection->addExpressionFieldToSelect(
            'sale_count',
            'SUM(i.qty_ordered)',
            'i.qty_ordered'
        );

        return $collection->getFirstItem()->getData('sale_count');
    }

    public function getSaleDailyEarnings()
    {
        $collection = $this->_prepareCollection();

        $collection->addExpressionFieldToSelect(
            'sale_amount',
            'SUM(i.price*i.qty_ordered)',
            'i.price'
        );

        $collection->getSelect()->group(
            [
                'MONTH(main_table.created_at)',
                'YEAR(main_table.created_at)',
            ]
        );

        return $collection->getData();
    }

    public function getSaleDailyItemsCount()
    {
        $collection = $this->_prepareCollection();

        $collection->addExpressionFieldToSelect(
            'sale_count',
            'SUM(i.qty_ordered)',
            'i.qty_ordered'
        );

        $collection->getSelect()->group(
            [
                'MONTH(main_table.created_at)',
                'YEAR(main_table.created_at)',
            ]
        );

        return $collection->getData();
    }

    public function getCurrencyHelper()
    {
        return $this->_currencyHelper;
    }
}
