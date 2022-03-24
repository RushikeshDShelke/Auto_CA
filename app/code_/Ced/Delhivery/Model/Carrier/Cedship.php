<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Delhivery\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Ups\Helper\Config;
use Magento\Framework\Xml\Security;
 
class Cedship extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'delhivery';
    protected $_logger;
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected  $_rateResultErrorFactory;
    protected $_objectManager;    

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Config $configHelper,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        array $data = []
    ) {
        $this->_localeFormat = $localeFormat;
        $this->configHelper = $configHelper;
        $this->_scopeConfig =  $scopeConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }
 

	public function collectRates(RateRequest $request) 
	{
		if (!$this->getConfigFlag('active')) {
			return false;
		}
		$result = $this->_rateResultFactory->create();
		$method = $this->_rateMethodFactory->create();
		$sourceCountry = $this->_scopeConfig->getValue('shipping/origin/country_id');
		$sourceZipcode = $this->_scopeConfig->getValue('shipping/origin/postcode');
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		if($request->getDestCountryId()!="IN" || $sourceCountry!="IN"){
		    return;
		}
		$destZipcode = $request->getDestPostcode();
		$zipcodeCollection = $this->_objectManager->create('Ced\Delhivery\Model\Pincode')->getCollection()->addFieldToFilter('pin',array($destZipcode,$sourceZipcode));
		if(!count($zipcodeCollection->getData())){
			return;
		}
		if($this->_scopeConfig->getValue('carriers/delhivery/shipping_mode')=='E' || $this->_scopeConfig->getValue('carriers/delhivery/shipping_mode')=='S'){
			$helper = $this->_objectManager->create('Ced\Delhivery\Helper\Data');
			$response = $this->_objectManager->create('Ced\Delhivery\Helper\Data')->codRequest($request);
			if(isset($response['error']))
				return false;
			
			$method->setCarrier($this->_code);
			$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethod($this->_code);
			$method->setMethodTitle($this->getConfigData('name'));
			$method->setPrice($response);
			$method->setCost($response);
			$result->append($method);
		}
		
		return $result;
    }
 

	public function getAllowedMethods()
	{
		return [$this->_code=> $this->getConfigData('name')];
	}

	public function isTrackingAvailable()
	{
	    return true;
	}

	protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
      
    }

    public function getTracking($trackings)
    {
    	$result = $this->_trackFactory->create();
    	$track_url = $this->getConfigData('track_url');
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('name'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("{$track_url}/{$tracking}");
            $result->append($status);
        }
        $this->_result = $result;
        return $result;
    }

    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request) {
        return true;
    }
	public function processAdditionalValidation(\Magento\Framework\DataObject $request) {
        return true;
    }
 
}
