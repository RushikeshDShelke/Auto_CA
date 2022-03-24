<?php
namespace Dotzotship\Simpleshipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'simpleshipping';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {

        $configPrice = $this->getConfigData('economy_cost');

        //$shippingPrice = $this->getFinalPriceWithHandlingFee($configPrice);

        //return $shippingPrice;
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $pcode = $cart->getQuote()->getShippingAddress()->getPostcode();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('wk_grid_records');
        
        
        $sql = "Select * FROM " . $tableName." where `pincode` = '$pcode'" ;
        $result = $connection->fetchall($sql); 
        if($result)
        {
            foreach($result as $listpincode)
            {
                $pinarray[] = $listpincode['product'];
            }
        }
        else
        {
           $pinarray[] = ''; 
        }
        
        $method_price = array();
        
        if(($this->getConfigData('enable_economy') == '1')  && (in_array('Economy',$pinarray)))
        {
            $method_price['Economy']['price'] = $this->getConfigData('economy_cost');
        }
        
        if(($this->getConfigData('enable_express') == '1')  && (in_array('Express',$pinarray)))
        {
            $method_price['Express']['price'] = $this->getConfigData('express_cost');
        }
        
        if(($this->getConfigData('enable_plus') == '1' && in_array('Plus',$pinarray)))
        {
            $method_price['Plus']['price'] = $this->getConfigData('plus_cost');
        }
        
        
        if(($this->getConfigData('enable_plus') == '0')  && ($this->getConfigData('enable_express') == '0') && ($this->getConfigData('enable_economy') == '0'))
        {
            $method_price[] = '';
        }
        
        return $method_price;

    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        

        $amount = $this->getShippingPrice();
        
        foreach($this->getShippingPrice() as $method => $price)
        {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($method);
                //$rate->setCost($price['price']);
                //$rate->setPrice($price['price']);
                $result->append($rate);
            
            
            
            
            /*$method->setPrice($price['price']);
            $method->setCost($price['price']);
            $method->setMethodTitle($methods);
            $result->append($method);*/
  
        }
            return $result; 
        
    }
    
    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $this->_getXmlTracking($trackings);

        return $this->_result;
    }
    
     protected function setTrackingReqeust()
    {
        $r = new \Magento\Framework\DataObject();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }
    public function isTrackingAvailable(){
            return true;
    }
}