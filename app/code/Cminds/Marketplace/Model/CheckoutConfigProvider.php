<?php

namespace Cminds\Marketplace\Model;

use Cminds\Marketplace\Helper\Supplier as SupplierHelper;
use Cminds\Marketplace\Model\Shipping\Carrier\Marketplace\Shipping as CarrierModel;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Cminds\Marketplace\Model\Supplier\Quote as SupplierQuote;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Directory\Model\CurrencyFactory;

/**
 * Cminds Marketplace checkout config provider model.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * Checkout session object.
     *
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Supplier helper object.
     *
     * @var SupplierHelper
     */
    protected $supplierHelper;

    /**
     * Customer registry object.
     *
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Supplier cart items array.
     *
     * @var Quote\Item[]
     */
    protected $supplierItems;

    /**
     * Supplier ids array.
     *
     * @var array
     */
    protected $supplierIds = [];

    /**
     * Store manager object.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Supplier Shipping Carrier object.
     *
     * @var CarrierModel
     */
    protected $carrierModel;

    /**
     * Supplier quote object.
     *
     * @var SupplierQuote
     */
    protected $supplierQuote;

    /**
     * Marketplace helper object.
     *
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * Currency Factory.
     *
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * CheckoutConfigProvider constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param SupplierHelper $supplierHelper
     * @param CustomerRegistry $customerRegistry
     * @param StoreManagerInterface $storeManager
     * @param CarrierModel $carrierModel
     * @param SupplierQuote $supplierQuote
     * @param MarketplaceHelper $marketplaceHelper
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        SupplierHelper $supplierHelper,
        CustomerRegistry $customerRegistry,
        StoreManagerInterface $storeManager,
        CarrierModel $carrierModel,
        SupplierQuote $supplierQuote,
        MarketplaceHelper $marketplaceHelper,
        CurrencyFactory $currencyFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->supplierHelper = $supplierHelper;
        $this->customerRegistry = $customerRegistry;
        $this->storeManager = $storeManager;
        $this->carrierModel = $carrierModel;
        $this->supplierQuote = $supplierQuote;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Quote object getter.
     * Retrieve it from checkout session.
     *
     * @return Quote
     */
    protected function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Return cart items which belongs to suppliers.
     *
     * @return array
     */
    protected function getSupplierItems()
    {
        if ($this->supplierItems === null) {
            $this->supplierItems = $this->supplierQuote->getSupplierItems();
            $this->supplierIds = $this->supplierQuote->getSupplierIds();
        }

        return $this->supplierItems;
    }

    /**
     * Return supplier shipping rates.
     *
     * @return array
     */
    protected function getSupplierRates()
    {
        $supplierItems = $this->getSupplierItems();

        $supplierRates = [];
        foreach ($supplierItems as $supplierId => $cartItems) {
            $supplierRates = array_merge_recursive(
                $supplierRates,
                $this->supplierHelper
                    ->getShippingMethods($supplierId, $cartItems)
            );
        }

        return $supplierRates;
    }

    /**
     * Return supplier names.
     *
     * @param array $supplierIds Supplier ids array.
     *
     * @return array
     */
    protected function getSupplierNames(array $supplierIds)
    {
        $supplierNames = [];
        foreach ($supplierIds as $supplierId) {
            try {
                $customer = $this->customerRegistry->retrieve($supplierId);
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $supplierName = $this->supplierHelper->getSupplierNameForShippingMethods($supplierId);

            $supplierNames[$supplierId] = __(
                'Supplier %1',
                $supplierName
            )->__toString();
        }

        return $supplierNames;
    }

    /**
     * Convert two-dimensional array of objects to arrays.
     * Structure supplier_id => DataObject[].
     *
     * @param array $supplierObjects Supplier objects array.
     *
     * @return array
     */
    protected function convertSupplierObjects(array $supplierObjects)
    {
        foreach ($supplierObjects as $key1 => $supplierObject) {
            if (is_array($supplierObject)) {
                foreach ($supplierObject as $key2 => $supplierSubObject) {
                    $supplierObjects[$key1][$key2] = $supplierSubObject->getData();
                }
            } else {
                $supplierObjects[$key1] = $supplierObject->getData();
            }
        }

        return $supplierObjects;
    }

    /**
     * Return supplier data.
     *
     * @return array
     */
    protected function getSupplierData()
    {
        $supplierItems = $this->convertSupplierObjects(
            $this->getSupplierItems()
        );
        $supplierNames = $this->getSupplierNames(array_keys($supplierItems));

        $supplierData = [];
        foreach ($supplierItems as $supplierId => $items) {
            if (!array_key_exists($supplierId, $supplierNames)) {
                continue;
            }

            $ratesData = [
                'items' => $supplierItems[$supplierId],
                'supplierId' => $supplierId,
                'supplierName' => $supplierNames[$supplierId],
            ];

            $supplierData[] = $ratesData;
        }

        return $supplierData;
    }

    /**
     * Return selected supplier shipping rates.
     *
     * @return array
     */
    protected function getSelectedSupplierShippingRates()
    {
        $selectedShippingMethods = $this->checkoutSession
            ->getMarketplaceShippingMethods();

        if (is_array($selectedShippingMethods) === false) {
            return [];
        }

        $selectedShippingMethods = array_map(
            function ($methodData) {
                return (int)$methodData['method_id'];
            },
            $selectedShippingMethods
        );
        $selectedShippingMethods = array_values($selectedShippingMethods);

        return $selectedShippingMethods;
    }

    /**
     * Return supplier rates data.
     *
     * @return array
     */
    protected function getSupplierShippingRatesData()
    {
        $supplierRates = $this->convertSupplierObjects(
            $this->getSupplierRates()
        );
        $selectedSupplierShippingRates = $this
            ->getSelectedSupplierShippingRates();

        foreach ($supplierRates as &$rate) {
            if (in_array($rate['id'], $selectedSupplierShippingRates)) {
                $rate['selected'] = 1;
            } else {
                $rate['selected'] = 0;
            }
        }

        return $supplierRates;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'supplierData' => $this
                ->getSupplierData(),
            'supplierShippingRates' => $this
                ->getSupplierShippingRatesData(),
            'baseUrl' => $this
                ->getBaseUrl(),
            'nonSupplierShippingPrice' => $this
                ->getSupplierShippingPriceNonSupplier(),
            'shippingMethodsEnabled' => $this->marketplaceHelper
                ->shippingMethodsEnabled(),
            'shippingMethodsExist' => count($this->getSupplierShippingRatesData()),
            'currency' => $this->getStoreCurrency(),
            'quoteShippindAddressDefaultCountry' => $this
                ->getQuote()
                ->getShippingAddress()
                ->getCountry()
        ];

        return $config;
    }

    /**
     * Get store currency.
     *
     * @return string
     */
    public function getStoreCurrency()
    {
        $currencyCode = $this->storeManager
            ->getStore()
            ->getCurrentCurrencyCode();


        $currencyFactory = $this->currencyFactory->create()
            ->load($currencyCode);

        return $currencyFactory->getCurrencySymbol();
    }

    /**
     * Return magento base url
     *
     * @return array
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_LINK);
    }

    /**
     * Get supplier shipping price for products without supplier
     *
     * @return int
     */
    public function getSupplierShippingPriceNonSupplier()
    {
        return $this->carrierModel->getSupplierShippingPriceNonSupplier();
    }
}
