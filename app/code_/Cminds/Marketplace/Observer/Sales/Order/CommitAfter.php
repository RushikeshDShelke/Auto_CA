<?php

namespace Cminds\Marketplace\Observer\Sales\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Cminds\Marketplace\Model\Torate;
use Cminds\Supplierfrontendproductuploader\Helper\Data;

class CommitAfter implements ObserverInterface
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Torate
     */
    protected $toRate;

    /**
     * @var Data
     */
    protected $supplierFrontendProductUploaderHelperData;

    public function __construct(
        ProductFactory $productFactory,
        OrderFactory $orderFactory,
        Torate $toRate,
        Data $data
    ) {
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->toRate = $toRate;
        $this->supplierFrontendProductUploaderHelperData = $data;
    }

    public function execute(Observer $observer)
    {
        if (!$this->supplierFrontendProductUploaderHelperData->isEnabled()) {
            return $this;
        }

        $order = $observer->getOrder();
        if (!$order->getCustomerId()) {
            return $this;
        }
        if ($order->getState() === Order::STATE_COMPLETE) {
            $items = $order->getAllItems();
            $orderId = $order->getId();
            foreach ($items as $item) {
                $product = $this->productFactory->create()->load($item->getProductId());
                if ($product->getCreatorId() === null) {
                    continue;
                }
                if ($product->getCreatorId() === $order->getCustomerId()) {
                    continue;
                }

                $toRateRecords = $this->toRate->getCollection()
                    ->addFieldToFilter('supplier_id', $product->getData('creator_id'))
                    ->addFieldToFilter('customer_id', $order->getCustomerId());
                if ($toRateRecords->count() > 0) {
                    continue;
                }
                $newToRateRecord = $this->toRate;
                $newToRateRecord
                    ->setSupplierId($product->getData('creator_id'))
                    ->setOrderId($orderId)
                    ->setProductId($item->getProductId())
                    ->setCustomerId($order->getCustomerId())
                    ->save();
            }
        }

        return $this;
    }
}
