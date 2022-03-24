<?php

namespace Cminds\Marketplace\Model;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;

class Rates extends AbstractModel
{
    const GLOBAL_MARKER = '*';
    
    protected $cart;
    protected $item;

    public function __construct(
        Cart $cart,
        Item $item,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null
    ) {
        $this->cart = $cart;
        $this->item = $item;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }

    protected function _construct()
    {
        $this->_init('Cminds\Marketplace\Model\ResourceModel\Rates');
    }

    public function getRateByWeight($country, $region, $postcode, $total = 0)
    {
        $unserilizedData = $this->unserializeRate();
        if (!$unserilizedData) {
            return false;
        }

        $total = $this->_validateTotal($total);

        $matched = $this->match($country, $region, $postcode, $total);
        $minValue = 0;
        foreach ($matched AS $i => $data) {
            if ((int)$data[3] > $total) {
                continue;
            }

            if ((int)$data[3] === $total) {
                $shippingCost = $data;
            }

            /** Check difference between current minimal value and the candidate number */
            $difference = $data[3] - $minValue;
            if ((int)$difference < 0) {
                continue;
            }

            $minValue = $data[3];
            $shippingCost = $data;
        }

        if (!isset($shippingCost)
            || $shippingCost == null
            && $total == 0 && count($matched) > 0
        ) {
            $shippingCost = $matched;
        }

        if (isset($shippingCost[4])
            && is_numeric($shippingCost[4])
        ) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    public function getRateByQty(
        $country = '*',
        $region = '*',
        $postcode = '*',
        $total = 0
    ) {

        $unserilizedData = $this->unserializeRate();
        if (!$unserilizedData) {
            return false;
        }

        $total = $this->_validateQtyTotal($total);

        $matched = $this->match($country, $region, $postcode, $total);

        foreach ($matched AS $i => $data) {
            if ($i == 1 && $total < $data[3]) {
                $shippingCost = $data;
                break;
            }
            if (isset($matched[$i + 1][3])
                && $data[3] <= $total
                && $total < $matched[$i + 1][3]
            ) {
                $shippingCost = $data;
                break;
            } elseif ($data[3] <= $total
                && !isset($matched[$i + 1][3])
            ) {
                $shippingCost = $data;
            }
        }

        if (!isset($shippingCost)
            || $shippingCost == null
            && $total == 0 && count($matched) > 0
        ) {
            $shippingCost = $matched;
        }

        if (isset($shippingCost[4])
            && is_numeric($shippingCost[4])
        ) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    public function getRateByPrice($total)
    {
        $unserilizedData = $this->unserializeRate();
        $shippingCost = [];

        if (!$unserilizedData) {
            return false;
        }

        $total = $this->_validatePriceTotal($total);

        foreach ($unserilizedData AS $i => $data) {
            if ($i == 1 && $total < $data[3]) {
                $shippingCost = $data;
                break;
            }
            if (isset($unserilizedData[$i + 1][3])
                && $data[3] <= $total
                && $total < $unserilizedData[$i + 1][3]
            ) {
                $shippingCost = $data;
                break;
            } elseif ($data[3] <= $total
                && !isset($unserilizedData[$i + 1][3])
            ) {
                $shippingCost = $data;
            }
        }

        if (isset($shippingCost[4]) && is_numeric($shippingCost[4])) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    public function getRate($country, $region, $postcode, $total)
    {
        $unserializedData = $this->unserializeRate();

        if (!$unserializedData) {
            return false;
        }

        $matched = $this->match($country, $region, $postcode, $total);

        if (count($matched) > 1 || count($matched) === 0) {
            return false;
        }

        $shippingCost = $matched[0];

        if (isset($shippingCost[4]) && is_numeric($shippingCost[4])) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    public function unserializeRate($getEmpty = false)
    {
        if ($this->getRateData() !== null) {
            return unserialize($this->getRateData());
        }

        return ($getEmpty) ? [] : false;
    }

    protected function match($country, $region, $postcode, $total)
    {
        $rates = $this->unserializeRate(true);
        $validCountries = [];
        foreach ($rates AS $rate) {
            if (strtoupper($rate[0]) === strtoupper($country)
                || $rate[0] === self::GLOBAL_MARKER
            ) {
                $validCountries[] = $rate;
            }
        }

        $validRegions = [];
        foreach ($validCountries AS $validCountry) {
            if (strpos($validCountry[1], ",") === false) {
                if ($validCountry[1] === $region
                    || $validCountry[1] === self::GLOBAL_MARKER
                ) {
                    $validRegions[] = $validCountry;
                }
            } else {
                $states = explode(',', $validCountry[1]);

                if (in_array($region, $states)) {
                    $validRegions[] = $validCountry;
                }
            }
        }

        $validZipCodes = [];
        foreach ($validRegions AS $validRegion) {
            if ($validRegion[2] === $postcode
                || $validRegion[2] === self::GLOBAL_MARKER
            ) {
                $validZipCodes[] = $validRegion;
            } else {
                $pc = explode('-', $postcode);
                $vR = explode('-', $validRegion[2]);

                if ($vR[0] === self::GLOBAL_MARKER && $pc[1] === $vR[1]) {
                    $validZipCodes[] = $validRegion;
                } elseif ($vR[1] === self::GLOBAL_MARKER && $pc[0] === $vR[0]) {
                    $validZipCodes[] = $validRegion;
                }

            }
        }

        return $validZipCodes;
    }

    protected function _validateTotal($total)
    {
        $cart = $this->cart;

        if ($total === 0) {
            if (!$cart->getForceItem()) {
                $items = $cart->getQuote()->getAllItems();
                foreach ($items AS $item) {
                    $total += $item->getWeight() * $item->getQty();
                }
            } else {
                $forcedItem = $cart->getForceItem();
                $item = $this->item->load($forcedItem);
                $total = $item->getWeight();
            }
        }

        return $total;
    }

    protected function _validateQtyTotal($total)
    {
        $cart = $this->cart;

        if ($total === 0) {
            if (!$cart->getForceItem()) {
                $items = $cart->getQuote()->getAllItems();
                foreach ($items AS $item) {
                    $total += $item->getQty();
                }
            } else {
                $total = 1;
            }
        }

        return $total;
    }

    protected function _validatePriceTotal($total)
    {
        $cart = $this->cart;

        if ($total === 0) {
            if (!$cart->getForceItem()) {
                $items = $cart->getQuote()->getAllItems();
                foreach ($items AS $item) {
                    $total += $item->getPrice() * $item->getQty();
                }
            } else {
                $forcedItem = $cart->getForceItem();
                $item = $this->item->load($forcedItem);
                $total = $item->getPrice();
            }
        }

        return $total;
    }
}
