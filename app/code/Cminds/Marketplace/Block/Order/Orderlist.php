<?php

namespace Cminds\Marketplace\Block\Order;

use Cminds\Marketplace\Helper\Data;
use Cminds\Marketplace\Helper\Profits;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as SalesOrderItem;
use Magento\Sales\Model\Order\Status;

class Orderlist extends Template
{
    protected $_marketplaceHelper;
    protected $_resourceConnection;
    protected $_salesOrderItem;
    protected $_objectManager;
    protected $_salesOrderStatus;
    protected $_salesOrder;
    protected $_currencyHelper;
    protected $_profitsHelper;

    public function __construct(
        Context $context,
        Data $cmindsHelper,
        ResourceConnection $resourceConnection,
        SalesOrderItem $salesOrderItem,
        ObjectManagerInterface $objectManagerInterface,
        Status $salesOrderStatus,
        Order $salesOrder,
        CurrencyHelper $coreHelper,
        Profits $profits
    ) {
        parent::__construct($context);

        $this->_marketplaceHelper = $cmindsHelper;
        $this->_resourceConnection = $resourceConnection;
        $this->_salesOrderItem = $salesOrderItem;
        $this->_objectManager = $objectManagerInterface;
        $this->_salesOrderStatus = $salesOrderStatus;
        $this->_salesOrder = $salesOrder;
        $this->_currencyHelper = $coreHelper;
        $this->_profitsHelper = $profits;

    }

    public function getFlatCollection()
    {
        $eavAttributeObject = $this->_objectManager
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');

        $eavAttribute = $eavAttributeObject;
        $supplier_id = $this->_marketplaceHelper->getSupplierId();
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table = "catalog_product_entity_int";
        $tableName = $this->_resourceConnection->getTableName($table);
        $orderTable = $this->_resourceConnection->getTableName('sales_order');

        $collection = $this->_salesOrderItem->getCollection();
        $collection->getSelect()
            ->join(
                ['o' => $orderTable],
                'o.entity_id = main_table.order_id',
                []
            )
            ->join(
                ['e' => $tableName],
                'e.entity_id = main_table.product_id AND e.attribute_id = ' . $code,
                []
            )
            ->where('main_table.parent_item_id is null')
            ->where('e.value = ?', $supplier_id)
            ->group('o.entity_id')
            ->order('o.entity_id DESC');

        if ($this->getFilter('autoincrement_id')) {
            $collection->getSelect()->where(
                'o.increment_id LIKE ?',
                "%" . $this->getFilter('autoincrement_id') . "%"
            );
        }
        if ($this->getFilter('status')) {
            $collection->getSelect()->where(
                'o.status = ?',
                $this->getFilter('status')
            );
        }

        if ($this->getFilter('from') && strtotime($this->getFilter('from'))) {
            $datetime = new \DateTime($this->getFilter('from'));
            $collection->getSelect()->where(
                'main_table.created_at >= ?',
                $datetime->format('Y-m-d') . " 00:00:00"
            );
        }
        if ($this->getFilter('to') && strtotime($this->getFilter('to'))) {
            $datetime = new \DateTime($this->getFilter('to'));
            $collection->getSelect()->where(
                'main_table.created_at <= ?',
                $datetime->format('Y-m-d') . " 23:59:59"
            );
        }

        return $collection;
    }

    private function getFilter($key)
    {
        return $this->getRequest()->getParam($key);
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function canCreateShipment(Order $order)
    {
        if ($order->getState() === 'canceled') {
            return false;
        }

        if ($order->getShippingAddress() === null) {
            return false;
        }

        $items = $this->getCurrentSupplierOrderItems($order);
        foreach ($items as $item) {
            if ($item->getQtyOrdered() - $item->getQtyShipped() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Order $order
     *
     * @return SalesOrderItem[]
     */
    public function getCurrentSupplierOrderItems(Order $order)
    {
        $items = $order->getItemsCollection();
        $supplierItems = [];

        foreach ($items as $item) {
            if ($this->_marketplaceHelper->isOwner($item->getProductId())) {
                $supplierItems[] = $item;
            }
        }

        return $supplierItems;
    }

    public function calculateSubtotal($order)
    {
        $subtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if ($this->_marketplaceHelper->isOwner($item->getProductId())) {
                $subtotal += $item->getPrice() * $item->getQtyOrdered();
            }
        }

        return $subtotal;
    }

    public function calculateDiscount($order)
    {
        $discount = 0;
        foreach ($order->getAllItems() AS $item) {
            if ($this->_marketplaceHelper->isOwner($item->getProductId())) {
                $discount += $item->getDiscountAmount();
            }
        }

        return $discount;
    }

    public function getMarketplaceHelper()
    {
        return $this->_marketplaceHelper;
    }

    public function getStoreConfig($path)
    {
        $scopeConfig = $this->_objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');

        return $scopeConfig->getValue($path);
    }

    public function getRequestParams($key, $defaultValue = null)
    {
        return $this->getRequest()->getParam($key, $defaultValue);
    }

    public function gestSalesOrderStatusModel()
    {
        return $this->_salesOrderStatus->getResourceCollection();
    }

    public function getSalesOrderModel()
    {
        return $this->_salesOrder;
    }

    public function getCurrencyHelper()
    {
        return $this->_currencyHelper;
    }

    public function getProfitsHelper()
    {
        return $this->_profitsHelper;
    }

    /**
     * @return bool
     */
    public function isDiscountEffective()
    {
        return (bool)$this->getStoreConfig(
            'configuration_marketplace/configure/is_discount_effective'
        );
    }

    /**
     * Get additional buttons html
     *
     * @param Order $order
     * @return mixed
     */
    public function getAdditionalRowButtons($order)
    {
        return $this->getChildBlock('additional.grid.action.buttons')->setOrder($order)->toHtml();
    }
}
