<?php

namespace Craft\Handdelivery\Model\Carrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;

class Customshipping extends AbstractCarrier implements CarrierInterface
{

/**

* Carrier's code
*
* @var string
*/

protected $_code = 'craft_handdelivery';
/**
* Whether this carrier has fixed rates calculation
*
* @var bool
*/

protected $_isFixed = true;
/**
* @var ResultFactory
*/

protected $_rateResultFactory;
/**
* @var MethodFactory
*/

protected $_rateMethodFactory;
/**
* @param ScopeConfigInterface $scopeConfig
* @param ErrorFactory $rateErrorFactory
* @param LoggerInterface $logger
* @param ResultFactory $rateResultFactory
* @param MethodFactory $rateMethodFactory
* @param array $data
*/

public function __construct(
ScopeConfigInterface $scopeConfig,
ErrorFactory $rateErrorFactory,
LoggerInterface $logger,
ResultFactory $rateResultFactory,
MethodFactory $rateMethodFactory,
array $data = []
) {

$this->_rateResultFactory = $rateResultFactory;
$this->_rateMethodFactory = $rateMethodFactory;
parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
}

/**
* Generates list of allowed carrier`s shipping methods
* Displays on cart price rules page
*
* @return array
* @api
*/

public function getAllowedMethods()
{
return [$this->getCarrierCode() => __($this->getConfigData('name'))];
}
/**
* Collect and get rates for storefront
*
* @SuppressWarnings(PHPMD.UnusedFormalParameter)
* @param RateRequest $request
* @return DataObject|bool|null
* @api
*/

public function collectRates(RateRequest $request)
{
return $false;
}

public function isTrackingAvailable()
{
return true;
}

}