<?php

namespace Cminds\Marketplace\Observer\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Catalog\Model\ProductFactory;
use Cminds\Marketplace\Helper\Supplier;

class UpdateShippingMethods implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Supplier
     */
    private $supplierHelper;

    /**
     * Plugin constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param ProductFactory $productFactory
     * @param Supplier $supplierHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductFactory $productFactory,
        Supplier $supplierHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productFactory = $productFactory;
        $this->supplierHelper = $supplierHelper;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();

        $quoteItems = $quote->getItems();
        if ($quoteItems === null) {
            $quoteItems = [];
        }

        $selectedShippingMethods = $this->checkoutSession->getMarketplaceShippingMethods();
        $newSelectedShippingMethods = [];
        $priceTotal = 0;

        foreach ($quoteItems as $item) {
            $product = $this->productFactory->create()
                ->load($item->getProductId());
            $creatorId = $product->getCreatorId();

            if (isset($selectedShippingMethods[$creatorId])) {
                $newSelectedShippingMethods[$creatorId] = $selectedShippingMethods[$creatorId];
            }

            $priceTotal = $this->supplierHelper
                ->calculateTotalShippingPrice($newSelectedShippingMethods);
        }

        $this->checkoutSession
            ->setMarketplaceShippingMethods($newSelectedShippingMethods)
            ->setMarketplaceShippingPrice($priceTotal);

        return $this;
    }
}
