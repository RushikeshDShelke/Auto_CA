<?php

namespace Cminds\Supplierfrontendproductuploader\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\PriceCurrency;

class Price extends AbstractHelper
{
    /**
     * Store Manager.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Currency Factory.
     *
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * Price Currency Object.
     *
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * Price constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param PriceCurrency $priceCurrency
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        PriceCurrency $priceCurrency,
        Context $context
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Convert price to base currency.
     *
     * @param $price
     *
     * @return float|int
     */
    public function convertToBaseCurrencyPrice($price)
    {
        if (!$price) {
            return 0;
        }

        $store = $this->storeManager->getStore();
        $rate = $this->priceCurrency->convert((double)$price, $store) / (double)$price;

        $amount = (double)$price / $rate;

        return $amount;
    }

    /**
     * Convert price to current store currency rate.
     *
     * @param $price
     *
     * @return float
     */
    public function convertToCurrentCurrencyPrice($price)
    {
        $price = $this->priceCurrency->convertAndRound($price);

        return $price;
    }

    /**
     * Get current currency symbol.
     *
     * @return null|string
     */
    public function getCurrentCurrencySymbol()
    {
        $currencyCode = $this->storeManager
            ->getStore()
            ->getCurrentCurrencyCode();

        $currency = $this->currencyFactory->create()
            ->load($currencyCode);
        if ($currency->getId()) {
            return $currency->getCurrencySymbol();
        }

        return null;
    }
}