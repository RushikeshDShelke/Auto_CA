<?php

namespace Cminds\Marketplace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Cminds\Marketplace\Model\ResourceModel\Methods\CollectionFactory;
use Cminds\Marketplace\Model\Methods;
use Magento\Framework\App\Helper\Context;
use Cminds\Marketplace\Model\Shipping\Carrier\Marketplace\Shipping;
use Magento\Quote\Model\Quote\Item;
use Cminds\Marketplace\Model\Config\Source\Shipping\GroupLabel;
use Magento\Customer\Model\CustomerFactory;
use Cminds\Marketplace\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Helper\Price;

/**
 * Cminds Marketplace supplier helper.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Supplier extends AbstractHelper
{
    /**
     * Collection factory object.
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Carrier object.
     *
     * @var Shipping
     */
    protected $carrier;

    /**
     * Helper object.
     *
     * @var Data
     */
    protected $marketplaceHelper;

    /**
     * Customer factory object.
     *
     * @var CustomerFactory
     */
    protected $customerFactory;

    protected $priceHelper;

    /**
     * Price Currency Object.
     *
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * Supplier constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Shipping $carrier
     * @param CustomerFactory $customerFactory
     * @param Data $marketplaceHelper
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Shipping $carrier,
        CustomerFactory $customerFactory,
        Data $marketplaceHelper,
        Price $price
    ) {
        parent::__construct($context);

        $this->collectionFactory = $collectionFactory;
        $this->carrier = $carrier;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerFactory = $customerFactory;
        $this->priceHelper = $price;
    }

    /**
     * Return shipping methods which belongs to supplier with provided id.
     *
     * @param int    $supplierId Supplier id.
     * @param Item[] $cartItems  Cart items array.
     *
     * @return Methods[]
     */
    public function getShippingMethods($supplierId, $cartItems)
    {
        $supplierRates = [];

        $collection = $this->collectionFactory->create();
        $collection->addFilter('supplier_id', $supplierId);

        foreach ($collection as $method) {
            $price = $this->carrier->getPrice($method, $cartItems);
            //$price = $this->priceHelper->convertToCurrentCurrencyPrice($price);
            $method->setPrice($price);
            $supplierRates[] = $method;
        }

        return $supplierRates;
    }

    /**
     * Get total shipping price by array with selected shipping methods
     *
     * @param array $shippingMethods Selected shipping methods data.
     *
     * @return float
     */
    public function calculateTotalShippingPrice($shippingMethods)
    {
        $totalPrice = 0;
        foreach ($shippingMethods as $method) {
            $totalPrice += $method['price'];
        }

        return $totalPrice;
    }

    /**
     * Get supplier name for supplier shipping methods.
     *
     * @param $supplierId
     *
     * @return string
     */
    public function getSupplierNameForShippingMethods($supplierId) {
        $customer = $this->customerFactory->create()->load($supplierId);
        $supplierName = $customer->getName();

        $checkConfig = (int)$this->carrier->getConfigData('shipping_methods_group_label') ===
            (int)GroupLabel::PROFILE_NAME;

        if ($this->marketplaceHelper->supplierPagesEnabled() &&
            !empty($customer->getSupplierName()) &&
            $checkConfig
        ) {
            $supplierName = $customer->getSupplierName();
        }

        return $supplierName;
    }


}
