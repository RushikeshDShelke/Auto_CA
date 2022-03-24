<?php

namespace Cminds\Marketplace\Model\Plugin\Quote\Address\RateResult;

use Magento\Quote\Model\Quote\Address\RateResult\Method as OrigMethod;

class Method
{
    /**
     * Remove rounding of the price for the shipping methods.
     *
     * @param OrigMethod $method
     * @param callable $proceed
     * @param $price
     *
     * @return int
     */
    public function aroundSetPrice(OrigMethod $method, callable $proceed, $price)
    {
        $result = $proceed($price);

        $result->setData('price', $price);

        return $result;
    }
}
