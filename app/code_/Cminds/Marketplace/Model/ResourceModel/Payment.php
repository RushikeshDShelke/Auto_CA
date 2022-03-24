<?php

namespace Cminds\Marketplace\Model\ResourceModel;

use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Framework\DataObject;

class Payment extends AbstractDb
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrderItemCollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        AttributeFactory $attributeFactory,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );

        $this->scopeConfig = $scopeConfig;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->attributeFactory = $attributeFactory;
    }

    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('marketplace_supplier_payments', 'id');
    }

    public function getAdminGridCollection()
    {
        $eavAttribute = $this->attributeFactory->create();
        $attributeId = $eavAttribute->getIdByCode(
            'catalog_product',
            'creator_id'
        );

        $productIntTable = $this->getTable('catalog_product_entity_int');
        $orderTable = $this->getTable('sales_order');
        $paymentTable = $this->getTable('marketplace_supplier_payments');

        $collection = $this->orderItemCollectionFactory->create()
            ->removeAllFieldsFromSelect()
            ->addFieldToSelect(['order_id']);

        $collection
            ->addExpressionFieldToSelect(
                'items_count',
                'count(main_table.item_id)',
                'main_table.item_id'
            )
            ->addExpressionFieldToSelect(
                'total_price',
                'sum(main_table.row_total)',
                'main_table.row_total'
            )
            ->addExpressionFieldToSelect(
                'total_qty',
                'sum(main_table.qty_ordered)',
                'main_table.qty_ordered'
            )
            ->addExpressionFieldToSelect(
                'total_vendor_income',
                'sum(main_table.vendor_income)',
                'main_table.vendor_income'
            )
            ->addExpressionFieldToSelect(
                'total_paid_amount',
                'ifnull((select sum(amount) from ' . $paymentTable
                . ' where order_id = main_table.order_id'
                . ' and supplier_id = pi.value), 0)',
                ['main_table.order_id', 'pi.value']
            );

        $collection->getSelect()
            ->join(
                ['pi' => $productIntTable],
                'pi.entity_id = main_table.product_id AND pi.attribute_id = ' . $attributeId,
                ['value as supplier_id']
            )
            ->join(
                ['o' => $orderTable],
                'o.entity_id = main_table.order_id',
                ['status', 'state', 'increment_id', 'created_at']
            )
            ->where('main_table.parent_item_id is null')
            ->where('pi.value is not null')
            ->where('o.state != "canceled"')
            ->group('main_table.order_id') // @codingStandardsIgnoreLine
            ->group('pi.value') // @codingStandardsIgnoreLine
            ->order('o.entity_id desc');

        return $collection;
    }

    /**
     * @param int $supplierId
     * @param int $orderId
     *
     * @return DataObject
     */
    public function getSupplierPaymentByOrderId($supplierId, $orderId)
    {
        $collection = $this->getAdminGridCollection();
        $collection->getSelect()
            ->where('pi.value = ?', $supplierId)
            ->where('main_table.order_id = ?', $orderId)
            ->limit(1);

        return $collection->getFirstItem(); // @codingStandardsIgnoreLine
    }

    public function getPaymentsByOrderId($orderId)
    {
        $collection = $this->getAdminGridCollection();
        $collection->getSelect()
            ->where('main_table.order_id = ?', $orderId);

        return $collection;
    }
}
