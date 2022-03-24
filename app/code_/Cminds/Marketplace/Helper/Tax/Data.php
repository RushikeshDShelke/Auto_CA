<?php

namespace Cminds\Marketplace\Helper\Tax;

use Magento\Tax\Helper\Data as OrigData;
use Magento\Customer\Model\Address;
use Magento\Store\Model\Store;

class Data extends OrigData
{
    /**
     * Get shipping price
     *
     * @param  float                      $price
     * @param  bool|null                  $includingTax
     * @param  Address|null               $shippingAddress
     * @param  int|null                   $ctc
     * @param  null|string|bool|int|Store $store
     * @return float
     */
    public function getShippingPrice($price, $includingTax = null, $shippingAddress = null, $ctc = null, $store = null)
    {
        $pseudoProduct = new \Magento\Framework\DataObject();
        $pseudoProduct->setTaxClassId($this->getShippingTaxClass($store));

        $billingAddress = false;
        if ($shippingAddress && $shippingAddress->getQuote() && $shippingAddress->getQuote()->getBillingAddress()) {
            $billingAddress = $shippingAddress->getQuote()->getBillingAddress();
        }

        $price = $this->catalogHelper->getTaxPrice(
            $pseudoProduct,
            $price,
            $includingTax,
            $shippingAddress,
            $billingAddress,
            $ctc,
            $store,
            $this->shippingPriceIncludesTax($store),
            false
        );

        return $price;
    }
}
