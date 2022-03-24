<?php

namespace Cminds\Marketplace\Controller\Order;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data;
use Cminds\Marketplace\Helper\Profits;
use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Export extends AbstractController
{
    protected $marketplaceHelper;
    protected $resourceConnection;
    protected $collectionFactory;
    protected $orderFactory;
    protected $currencyHelper;
    protected $resultRawFactory;
    protected $attributeFactory;
    protected $profitsHelper;

    /**
     * Object constructor.
     *
     * @param Context               $context
     * @param Helper                $helper
     * @param Data                  $cmindsHelper
     * @param ResourceConnection    $resourceConnection
     * @param CollectionFactory     $collectionFactory
     * @param OrderFactory          $orderFactory
     * @param CurrencyHelper        $coreHelper
     * @param RawFactory            $rawFactory
     * @param AttributeFactory      $attributeFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     * @param Profits $profitHelper
     */
    public function __construct(
        Context $context,
        Helper $helper,
        Data $cmindsHelper,
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory,
        OrderFactory $orderFactory,
        CurrencyHelper $coreHelper,
        RawFactory $rawFactory,
        AttributeFactory $attributeFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Profits $profitHelper
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->marketplaceHelper = $cmindsHelper;
        $this->resourceConnection = $resourceConnection;
        $this->collectionFactory = $collectionFactory;
        $this->orderFactory = $orderFactory;
        $this->attributeFactory = $attributeFactory;
        $this->currencyHelper = $coreHelper;
        $this->resultRawFactory = $rawFactory;
        $this->profitHelper = $profitHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @return Raw
     */
    public function execute()
    {
        $orderItemsCollection = $this->getFlatCollection();
        $productCsv = [];

        foreach ($orderItemsCollection as $orderItem) {
            $order = $this->orderFactory->create()
                ->load($orderItem->getOrderId());

            $subtotal = $this->calculateSubtotal($order);
            $income = $this->profitHelper->calculateNetIncome($this->marketplaceHelper->getSupplierId(), $subtotal);

            $productCsv[] = [
                'Order #' => '#'.$order->getIncrementId(),
                'Purchased On' => $order->getCreatedAt(),
                'Bill to Name' => $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname(),
                'Ship to Name'=> $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname(),
                'Subtotal' => $this->currencyHelper->currency($subtotal, true, false),
                'Income' => $this->currencyHelper->currency($income, true, false),
                'Status' => $order->getStatus(),
            ];
        }

        $this->marketplaceHelper
            ->prepareCsvHeaders('order_export_' . date('Y-m-d') . '.csv');

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->marketplaceHelper->array2Csv($productCsv));

        return $resultRaw;
    }

    /*
     * Retrive Calculated subtotal for Supplier
     * 
     * @return Float
     * 
     */
    public function calculateSubtotal($order)
    {
        $subtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if ($this->marketplaceHelper->isOwner($item->getProductId())) {
                $subtotal += $item->getPrice() * $item->getQtyOrdered();
            }
        }

        return $subtotal;
    }

    /**
     * Retrieve filtered collection.
     *
     * @return Collection
     */
    public function getFlatCollection()
    {
        $supplierId = $this->marketplaceHelper->getSupplierId();

        $eavAttribute = $this->attributeFactory->create();
        $attributeId = $eavAttribute->getIdByCode(
            'catalog_product',
            'creator_id'
        );

        $productIntTable = $this->resourceConnection
            ->getTableName('catalog_product_entity_int');
        $orderTable = $this->resourceConnection
            ->getTableName('sales_order');

        $collection = $this->collectionFactory->create();
        $collection
            ->getSelect()
            ->join(
                ['o' => $orderTable],
                'o.entity_id = main_table.order_id',
                []
            )
            ->join(
                ['e' => $productIntTable],
                'e.entity_id = main_table.product_id AND e.attribute_id = ' . $attributeId,
                []
            )
            ->where('main_table.parent_item_id is null')
            ->where('e.value = ?', $supplierId)
            ->order('o.entity_id DESC');

        if ($this->getFilter('autoincrement_id')) {
            $collection
                ->getSelect()
                ->where(
                    'o.increment_id LIKE ?',
                    '%' . $this->getFilter('autoincrement_id') . '%'
                );
        }
        if ($this->getFilter('status')) {
            $collection
                ->getSelect()
                ->where(
                    'o.status = ?',
                    $this->getFilter('status')
                );
        }
        if ($this->getFilter('from') && strtotime($this->getFilter('from'))) {
            $datetime = new \DateTime($this->getFilter('from'));
            $collection
                ->getSelect()
                ->where(
                    'main_table.created_at >= ?',
                    $datetime->format('Y-m-d') . ' 00:00:00'
                );
        }
        if ($this->getFilter('to') && strtotime($this->getFilter('to'))) {
            $datetime = new \DateTime($this->getFilter('to'));
            $collection
                ->getSelect()
                ->where(
                    'main_table.created_at <= ?',
                    $datetime->format('Y-m-d') . ' 23:59:59'
                );
        }

        return $collection;
    }

    /**
     * Retrieve particular filter value.
     *
     * @param string $key
     *
     * @return mixed
     */
    private function getFilter($key)
    {
        return $this->getRequest()->getParam($key);
    }
}
