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
namespace Ced\Delhivery\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
   
	protected $_objectManager;

	public function __construct(
			\Magento\Framework\App\Helper\Context $context,
			\Magento\Framework\ObjectManagerInterface $objectManager
	) {
		$this->_objectManager = $objectManager;
		parent::__construct($context);
	}
	/**
	 * 
	 * 
	 * @return string
	 */
    public function getLastmileUrl(){
        return $this->_getUrl('lastmile');
    }
	/*
	* Function to execute curl
	* @return API response
	*/
    public function Executecurl($url, $type, $params)
    {

$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test13may.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('hiiiiiii');
$logger->info($url);

    	 try {   
			 $token=$this->scopeConfig->getValue('carriers/delhivery/license_key',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $ch = curl_init();
  		curl_setopt($ch, CURLOPT_URL,$url);
  		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
  		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
  		if($type == 'POST'):
  			curl_setopt($ch, CURLOPT_POST, true);
  			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
  			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token '.$token,
                                                    'Content-Type: application/json'));
		  endif;


		  $retValue = curl_exec($ch);
    	$info = curl_getinfo($ch);

    	$err=curl_error($ch);
$logger->info($err);
   		if($errno = curl_errno($ch)) {
   			  $error_message = curl_strerror($errno);
   		}
		    curl_close($ch);
		  return $retValue;     
    	}
    	catch(\Exception $e)
    	{     
    		return	$e->getMessage();
    	}
			
    }
    public function Trackcurl($url, $type)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_TIMEOUT, 60); 
            $retValue = curl_exec($ch);
            $err=curl_error($ch);
            if($errno = curl_errno($ch)) {
                $error_message = curl_strerror($errno);
                
            }
            curl_close($ch);
            return $retValue;
        }
        catch(\Exception $e)
        {
            return  $e->getMessage();
        }
    }
    
    public function codRequest($request)
    {
    	$token=$this->scopeConfig->getValue('carriers/delhivery/license_key',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$client = $this->scopeConfig->getValue('carriers/delhivery/client_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$shippingCharges  = $this->scopeConfig->getValue('carriers/delhivery/shipping_charge',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$originzipcode = $this->scopeConfig->getValue('shipping/origin/postcode',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$packageWeight = $request->getPackageWeight();
    	if(!$packageWeight){
            $packageWeight = 0.01;
        }
        $destZipcode =$request->getDestPostcode();
    	$md = $this->scopeConfig->getValue('carriers/delhivery/shipping_mode' ,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$quoteobj=$this->_objectManager->create('Magento\Checkout\Model\Cart')->getQuote();
    	$ordertotal = $quoteobj->getBaseGrandTotal();
    	try{
    		if($destZipcode && $originzipcode)
    		{
                $queryParamscod = array(
                  "cl" => $client,
                  "ss" => "RTO",
                  "md" => $md,
                  "pt" => 'Pre-paid',
                  "token" => $token,
                  "gm" => $packageWeight,
                  "o_pin" => $originzipcode,
                  "d_pin" =>$destZipcode, 
                );
	    		$responseerror = [];
	    		$postageTypesURL = $shippingCharges.'.json?' .
	    				http_build_query($queryParamscod);

	    		$ch = curl_init();
	    		curl_setopt($ch,CURLOPT_URL,$postageTypesURL);
	    		curl_setopt($ch,CURLOPT_FAILONERROR, 1);
	    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    		curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
	    		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	    		curl_setopt($ch,CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token '.$token,
                                                    'Content-Type: application/json'));
	    		$response = curl_exec($ch);
	    		$response= json_decode($response,true);  	
	    		curl_close($ch);
		    	if(!$response || isset($response['error']))
		    	{
		    	   $response = array('error'=>'error');
		    	}
                if(isset($response[0])){
                    if(is_array($response[0]) && $response[0]['total_amount'])
                        $response = $response[0]['total_amount'];
                    else
                        $response = array('error'=>'error');
                }else{
                    $response = array('error'=>'error');
                }
    	     
    		}
    		else{
    			$response = array('error'=>'error');
    		}
            return $response;
    	}catch(\Exception $e){
    		$e->getMessage();
    	}
   }
    
   
   public function fetchWaybill()
   {
   	    try {
       	    $token = $this->scopeConfig->getValue('carriers/delhivery/license_key',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $client = $this->scopeConfig->getValue('carriers/delhivery/login_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
            $url = "https://track.delhivery.com/waybill/api/fetch/json/?token=".$token."&cl=".$client;
       
       		$ch = curl_init();
       		curl_setopt($ch,CURLOPT_URL,$url);
       		curl_setopt($ch,CURLOPT_FAILONERROR, 1);
       		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
       		curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
       		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
       		curl_setopt($ch,CURLOPT_TIMEOUT, 60);
       		$retValue = curl_exec($ch);
       		$err=curl_error($ch);
       		if($errno = curl_errno($ch)) {
       			$error_message = curl_strerror($errno);
       		}
       		if(!$retValue)
       			return 'error';
       		return $retValue;
   	    }
       	catch(\Exception $e)
       	{
       	return  $e->getMessage();
       	
       	}
    }
   
    public function PickupRequest($url, $type, $params)
    {
      $token = $this->scopeConfig->getValue('carriers/delhivery/license_key',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch,CURLOPT_FAILONERROR, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true); 
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch,CURLOPT_TIMEOUT, 60);
      if($type == 'POST'):
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                                                            'Authorization: Token '.$token,
                                                            'Accept: application/json'));
      endif;  
        $retValue = curl_exec($ch);
        $retValue = json_decode($retValue,true);
        $err=curl_error($ch);
        if(!$retValue)
        {
          return false;
        }
        curl_close($ch);
        return $retValue;
    }
    
    function getItemAmount($item){
        $amount = ($item->getRowTotal () + $item->getTaxAmount () + $item->getHiddenTaxAmount () + $item->getWeeeTaxAppliedRowAmount () - $item->getDiscountAmount ())/$item->getQtyOrdered();
        return $amount;
    }
}
