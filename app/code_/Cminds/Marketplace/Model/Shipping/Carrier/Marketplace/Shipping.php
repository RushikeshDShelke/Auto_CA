<?php

namespace Cminds\Marketplace\Model\Shipping\Carrier\Marketplace;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Catalog\Model\ProductFactory;
use Cminds\Marketplace\Model\Methods;
use Cminds\Marketplace\Model\Rates;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;

class Shipping extends AbstractCarrier
{
    /**
     * Code field is duplicated intentionally.
     * - Some magento 2 version are referring to this field with underscore
     * (for example 2.1.3).
     * - Some requires this field without underscore (for example 2.1.2).
     */
    protected $_code = 'supplier';
    protected $code = 'supplier';

    protected $request = null;
    protected $result = null;
    protected $data = [];
    protected $supplierShippingPrices = [];
    protected $resultFactory;
    protected $methodFactory;
    protected $productFactory;
    protected $methods;
    protected $supplierRates;
    protected $cart;
    protected $session;
    /**
     * Marketplace helper object.
     *
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    public function __construct(
	CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $result,
        MethodFactory $methodFactory,
        ProductFactory $product,
        Methods $methods,
        Rates $supplierRates,
        Cart $cart,
        Session $session,
        MarketplaceHelper $marketplaceHelper
    ) {
	$this->customerSession = $customerSession;
        $this->resultFactory = $result;
        $this->methodFactory = $methodFactory;
        $this->productFactory = $product;
        $this->methods = $methods;
        $this->supplierRates = $supplierRates;
        $this->cart = $cart;
        $this->session = $session;
        $this->marketplaceHelper = $marketplaceHelper;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger
        );
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || 
            !$this->marketplaceHelper->shippingMethodsEnabled()) {
            return false;
        }

        $this->setRequest($request);
        $this->collectData($request);

        $result = $this->resultFactory->create();

        $method = $this->methodFactory->create();
        $method->setCarrier('supplier');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('supplier');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($this->getTotalPrice());
        
        $result->append($method);
        $this->result = $result;

        return $result;
    }

    public function setRequest(RateRequest $request)
    {
        $this->request = $request;

        $r = new DataObject();

        $this->rawRequest = $r;

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'FREIGHT' => __('Freight'),
            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            if ($k === '') {
                $arr = $this->getCode('method', $k);
            } else {
                $arr[$k] = $this->getCode('method', $k);
            }
        }

        return $arr;
    }

    public function proccessAdditionalValidation(DataObject $request)
    {
        if (!count($request->getAllItems())) {
            return $this;
        }

        $errorMsg = '';
        $showMethod = $this->getConfigData('show_method_if_not_applicable');

        if (!$errorMsg
            && !$request->getDestPostcode()
            && $this->isZipCodeRequired()
        ) {
            $errorMsg = __(
                'This shipping method is not available, please specify ZIP-code'
            );
        }

        if ($errorMsg && $showMethod) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorMsg);

            return $error;
        } elseif ($errorMsg) {
            return false;
        }

        return $this;
    }

    protected function getTotalPrice()
    {
        return max(
            [
                $this->getConfigData('minimum_price_supplier_shipped_products'),
                $this->getSupplierTotalPrice(),
            ]
        );
    }

    protected function getSupplierTotalPrice()
    {
        return $this->session->getMarketplaceShippingPrice();
    }

    public function getSupplierShippingPriceNonSupplier()
    {
        return $this->getConfigData('shipping_cost_non_supplier_products');
    }

    protected function collectData($request)
    {
        $totalItems = [];
        $all_items = $request->getAllItems();

        foreach ($all_items as $item) {
            if ($item->getProductType() != 'configurable') {
                $qty = $item->getQty();
                $product = $this->productFactory->create()
                    ->load($item->getProductId());

                if ($product->getCreatorId()) {
                    $totalItems[$product->getCreatorId()][] = [
                        $product->getWeight(),
                        $item->getPrice(),
                        $qty,
                    ];
                } else {
                    $this->data['prices'][] = $this->getConfigData(
                        'shipping_cost_non_supplier_products'
                    );
                }
            }
        }

        foreach ($totalItems as $supplierId => $items) {
            $costModel = $this->methods
                ->load($supplierId, 'supplier_id');
            if ($costModel->getId()) {
                $cost = $this->getPrice($costModel, $items);

                if ($cost === false) {
                    $this->data['prices'][] = $this->getConfigData('title');
                } else {
                    $this->data['prices'][] = $cost;
                }
            } else {
                $this->data['prices'][] = $this->getConfigData(
                    'default_shipping_cost_supplier_not_set_shipping_prices'
                );
            }
        }
    }

    public function getPrice($supplierPriceModel, $items)
    {
        if ($supplierPriceModel->getFreeShipping()) {
            return 0;
        }
        if ($supplierPriceModel->getFlatRateAvailable()) {
            return $supplierPriceModel->getFlatRateFee();
        }
        if ($supplierPriceModel->getTableRateAvailable()) {
            $supplerRates = $this->supplierRates
                ->load($supplierPriceModel->getSupplierId(), 'supplier_id');
            if (!$supplerRates->getId()) {
                return $supplierPriceModel->getTableRateFee();
            }
            $calculatedRate = $this->calculateTableFee(
                $items,
                $supplerRates,
                $supplierPriceModel->getTableRateCondition()
            );

            if ($calculatedRate === false) {
                $calculatedRate = $supplierPriceModel->getTableRateFee();
            }

            return $calculatedRate;
        }

        return 0.0;
    }

    protected function calculateTableFee($items, $model, $type)
    {
        $cart = $this->cart;
        $country = $this->session->getCountryVal();

        if ($country === '') {
            if (!$this->customerSession->getCustomer()) {
                $country = $cart
                    ->getQuote()
                    ->getShippingAddress()
                    ->getCountry();
            } else {
                $country = $this->customerSession
                    ->getCustomer()
                    ->getDefaultShippingAddress()
                    ->getCountry();
            }
        }

        $this->session->setCountryVal($country);
        
        $region = $cart->getQuote()->getShippingAddress()->getRegionCode();
        $postcode = $cart->getQuote()->getShippingAddress()->getPostcode();

        $total = 0;

        foreach ($items as $item) {
            if (isset($item[$type - 1])) {
                if ($type == 3) {
                    $total += $item[$type - 1];
                } else {
                    $total += ($item[$type - 1] * $item[2]);
                }
            }
        }

        if ($type == 1) {
            return $model->getRateByWeight(
                $country,
                $region,
                $postcode,
                $total
            );
        } else {
            if ($type == 2) {
                return $model->getRateByPrice(
                    $country,
                    $region,
                    $postcode,
                    $total
                );
            } else {
                if ($type == 3) {
                    return $model->getRateByQty(
                        $country,
                        $region,
                        $postcode,
                        $total
                    );
                }
            }
        }

        return 0;
    }
}
