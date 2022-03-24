<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Item;

class Soldproducts extends Extended
{
    protected $_registry;
    protected $_resourceConnection;
    protected $_orderItem;
    protected $_objectManager;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ResourceConnection $resourceConnection,
        Item $orderItem,
        ObjectManagerInterface $objectManagerInterface,
        Registry $registry,
        array $data
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->_registry = $registry;
        $this->_resourceConnection = $resourceConnection;
        $this->_orderItem = $orderItem;
        $this->_objectManager = $objectManagerInterface;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('customer_sold_products');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $eavAttributeObject = $om->create(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );

        $eavAttribute = $eavAttributeObject;
        $supplier_id = $this->_request->getParam('id');
        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $table = "catalog_product_entity_int";
        $tableName = $this->_resourceConnection->getTableName($table);
        $orderTable = $this->_resourceConnection->getTableName('sales_order');

        $collection = $this->_orderItem->getCollection();
        $collection->addExpressionFieldToSelect(
            'vendor_amount_with_discount',
            'SUM((row_total-main_table.discount_amount)'
            . '-((row_total-main_table.discount_amount)*(vendor_fee/100)))',
            'vendor_fee'
        );
        $collection->getSelect()
            ->join(
                ['o' => $orderTable],
                'o.entity_id = main_table.order_id',
                [
                    "CONCAT(`customer_firstname`, ' ', `customer_lastname`) "
                    . "AS customer_name",
                    "",
                ]
            )
            ->join(
                ['e' => $tableName],
                'e.entity_id = main_table.product_id AND e.attribute_id = ' . $code,
                []
            )
            ->where('main_table.parent_item_id is null')
            ->where('e.value = ?', $supplier_id)
            ->group('o.entity_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $scopeConfig = $this->_objectManager->create(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        );
        $isDiscountEff = $scopeConfig
            ->getValue('configuration_marketplace/configure/is_discount_effective');

        $this->addColumn(
            'name',
            [
                'header' => __('Product name'),
                'width' => '100',
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'quantity',
            [
                'header' => __('Quantity'),
                'width' => '100',
                'index' => 'qty_ordered',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchase On'),
                'index' => 'created_at',
                'filter_index' => 'o.created_at',
                'type' => 'datetime',
            ]
        );
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Shipped to Name'),
                'index' => 'customer_name',
            ]
        );
        $this->addColumn(
            'sub_total',
            [
                'header' => __('Subtotal'),
                'index' => 'row_total',
                'type' => 'currency',
                'currency' => 'order_currency_code',
            ]
        );
        $this->addColumn(
            'net_income',
            [
                'header' => __('Net Income'),
                'index' => 'row_total',
            ]
        );

        if ($isDiscountEff) {
            $this->addColumn(
                'discount_amount',
                [
                    'header' => __('Discount'),
                    'width' => '100',
                    'index' => 'discount_amount',
                    'type' => 'price',
                ]
            );
            $this->addColumn(
                'vendor_amount_with_discount',
                [
                    'header' => __('Net Income With Discount'),
                    'width' => '100',
                    'index' => 'vendor_amount_with_discount',
                    'type' => 'price',
                ]
            );
        }

        // TODO: Add order_id to the link.
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'order_id',
                'filter' => false,
                'sortable' => false,
                'actions' => [
                    [
                        'caption' => __('View Order'),
                        'url' => $this->getUrl(
                            '*/sales_order/view/order_id/$order_id'
                        ),
                        'order_id' => 'order_id',
                    ],
                ],
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            'marketplace/customer/soldproducts',
            ['_current' => true]
        );
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $row->getOrderId()]
        );
    }
}
