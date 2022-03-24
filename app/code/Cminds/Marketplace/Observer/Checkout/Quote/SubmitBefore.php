<?php

namespace Cminds\Marketplace\Observer\Checkout\Quote;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Cminds\Marketplace\Model\Shipping\Carrier\Marketplace\Shipping;
use Cminds\Marketplace\Helper\Supplier as SupplierHelper;
use Cminds\Marketplace\Model\Supplier\Quote as SupplierQuote;

/**
 * Cminds Marketplace quote submit before observer.
 * Will be executed on "checkout_submit_before" event.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class SubmitBefore implements ObserverInterface
{
    /**
     * Checkout session object.
     *
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Shipping object.
     *
     * @var Shipping
     */
    protected $shipping;

    /**
     * Supplier helper object.
     *
     * @var SupplierHelper
     */
    protected $supplierHelper;

    /**
     * Supplier quote object.
     *
     * @var SupplierQuote
     */
    protected $supplierQuote;

    /**
     * Object initialization.
     *
     * @param CheckoutSession $checkoutSession Checkout session object.
     * @param Shipping        $shipping        Shipping object.
     * @param SupplierHelper  $supplierHelper  Supplier helper object.
     * @param SupplierQuote   $supplierQuote   Supplier quote object.
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Shipping $shipping,
        SupplierHelper $supplierHelper,
        SupplierQuote $supplierQuote
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shipping = $shipping;
        $this->supplierHelper = $supplierHelper;
        $this->supplierQuote = $supplierQuote;
    }

    /**
     * Quote submit before event handler.
     *
     * @param Observer $observer Observer object.
     *
     * @return SubmitBefore
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $quoteModel = $this->checkoutSession->getQuote();

        $shippingAmount = $quoteModel->getShippingAddress()->getShippingAmount();
        $shippingMethod = $quoteModel->getShippingAddress()->getShippingMethod();

        $shippingMethodParts = explode('_', $shippingMethod);
        if (!isset($shippingMethodParts[0])) {
            return $this;
        }

        $carrierCode = $this->shipping->getCarrierCode();
        if ($shippingMethodParts[0] !== $carrierCode) {
            return $this;
        }

        $supplierIds = $this->supplierQuote->getSupplierIds();
        $supplierMethods = $this->checkoutSession->getMarketplaceShippingMethods()
            ?: [];

        $methodsCheck = array_diff($supplierIds, array_keys($supplierMethods));

        $supplierMethodsPrice = $this->supplierHelper
            ->calculateTotalShippingPrice($supplierMethods);
        $amountCheck = (float)$shippingAmount === (float)$supplierMethodsPrice;

        if (empty($methodsCheck) && $amountCheck === true) {
            return $this;
        }

//        throw new LocalizedException(
//            __('Please specify shipping method for each supplier..')
//        );
    }
}
