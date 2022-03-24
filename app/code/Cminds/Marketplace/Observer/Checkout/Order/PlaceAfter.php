<?php

namespace Cminds\Marketplace\Observer\Checkout\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Cminds Marketplace order place after observer.
 * Will be executed on "sales_order_place_after" event.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class PlaceAfter implements ObserverInterface
{
    /**
     * Checkout session object.
     *
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Object initialization.
     *
     * @param CheckoutSession $checkoutSession Checkout session object.
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Order place after event handler.
     *
     * @param Observer $observer Observer object.
     *
     * @return PlaceAfter
     */
    public function execute(Observer $observer)
    {
        $this->checkoutSession
            ->unsMarketplaceShippingMethods()
            ->unsMarketplaceShippingPrice();

        return $this;
    }
}
