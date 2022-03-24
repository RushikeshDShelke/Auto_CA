<?php

namespace Cminds\Marketplace\Observer;

use Cminds\Marketplace\Helper\Profits as MarketplaceProfitsHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierfrontenduploaderHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\ItemFactory;

class SaveCommission implements ObserverInterface
{
    private $marketplaceProfitsHelper;
    private $supplierfrontenduploaderHelper;
    private $productFactory;
    private $itemFactory;
    private $session;

    public function __construct(
        MarketplaceProfitsHelper $marketplaceHelper,
        ProductFactory $product,
        ItemFactory $itemFactory,
        SupplierfrontenduploaderHelper $supplierfrontenduploaderHelper,
        Session $session
    ) {
        $this->marketplaceProfitsHelper = $marketplaceHelper;
        $this->supplierfrontenduploaderHelper = $supplierfrontenduploaderHelper;
        $this->productFactory = $product;
        $this->itemFactory = $itemFactory;
        $this->session = $session;
    }

    public function execute(Observer $observer)
    {
        if (!$this->supplierfrontenduploaderHelper->isEnabled()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $shippingMethodsSelected = $this->session->getMarketplaceShippingMethods();
        $items = $order->getAllItems();

        foreach ($items as $item)
        {
            $product = $this->productFactory->create()
                ->load($item['product_id']);

            if ($product->getCreatorId() === null
                || $product->getCreatorId() === 0
            ) {
                continue;
            }

            $orderItem = $this->itemFactory->create()
                ->load($item['item_id']);
            if (!$orderItem->getId()) {
                continue;
            }

            $vendorIncomes = $this->marketplaceProfitsHelper
                ->getVendorIncome($product, $item['price'] * $item['qty_ordered']);

            if ($vendorIncomes) {
                $orderItem
                    ->setVendorFee($vendorIncomes['percentage'])
                    ->setVendorIncome($vendorIncomes['income']);
            }

            if (isset($shippingMethodsSelected[$product->getData('creator_id')])) {
                $orderItem->setShippingPrice(
                    $shippingMethodsSelected[$product->getData('creator_id')]['price']
                );
                $orderItem->setShippingMethodId(
                    $shippingMethodsSelected[$product->getData('creator_id')]['method_id']
                );

                unset($shippingMethodsSelected[$product->getData('creator_id')]);
            }

            $orderItem->save();
        }

        return $this;
    }
}
