<?php

namespace Kellton\Externallink\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;   
    private   $_paytmhelper;
    protected $_customerFactory;
    protected $_datetime;
    protected $_smsbulk;
    protected $_salesorder;
    protected $_urlInterface;

    const XML_PATH_MODULE_Paytm      = 'payment/paytm/';
    const XML_PATH_MODULE_GlobalKey  = 'global/crypt/';

    public function __construct(
    	Context $context,       
        StoreManagerInterface $storeManager, 
        \Magento\Framework\UrlInterface $urlInterface,
        \One97\Paytm\Helper\Data $paytmhelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Kellton\Smsbulk\Model\Smsbulk $smsbulk,
        \Magento\Sales\Model\Order $salesorder
        )
    {        
        $this->storeManager  = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_paytmhelper  = $paytmhelper;
        $this->_customerFactory = $customerFactory;
        $this->_datetime    = $datetime;
        $this->_smsbulk     = $smsbulk;
        $this->_salesorder  = $salesorder;
        parent::__construct($context);
    }

     public function getCustomerById($id) {
        return $this->_customerFactory->create()->load($id);
    }
 

    public function getConfigValue($field, $storeId = null)
    {

        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }


    public function isEnabled($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_MODULE_GENERAL  . $code, $storeId);
    }

     public function getStoreId()
    {

        return $this->storeManager->getStore()->getStoreId();
    }

     public function getDatetime()
    {

        return $this->_datetime;
    }

    public function getsms(){

    	return $this->_smsbulk->create();
    }
    

     public function geBaseUrl()
    {

        return $this->storeManager->getStore()->getBaseUrl();
    }


    public function getFormFields()
    {
		try{
			//echo $orderIncrement_Id = base64_decode($_GET['id']); // this is entity id 
			
			$orderIncrement_Id = $_GET['id'];

            $storeId   = $this->storeManager->getStore()->getId();
			$store     = $this->storeManager->getStore($storeId);
            $websiteId = $store->getWebsiteId();


			$order = $this->_salesorder->create()->loadByIncrementId($orderIncrement_Id);
			$orderId = $order->getRealOrderId();

			if($order->getId()){
				if($order->getStatus() == 'pending_payment')
				{
					$orderId = $order->getRealOrderId()."_2";
				}

				$price      = number_format($order->getGrandTotal(),2,'.','');
				$currency   = $order->getOrderCurrencyCode();

				// Need for paytm payment gateways module dependencies....

				// $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());

				$const = $this->getConfigValue(self::XML_PATH_MODULE_GlobalKey  .'key', $storeId);

				$paytm_inst_key = $this->getConfigValue(self::XML_PATH_MODULE_Paytm  .'merchant_key', $storeId);

				$paytm_inst_id = $this->getConfigValue(self::XML_PATH_MODULE_Paytm  .'MID', $storeId);

                $mer = $this->_paytmhelper->decrypt_e($paytm_inst_key,$const);
                $merid = $this->_paytmhelper->decrypt_e($paytm_inst_id,$const);

                $industry_type = $this->getConfigValue(self::XML_PATH_MODULE_Paytm  .'Industry_id', $storeId);
				$is_callback   = $this->getConfigValue(self::XML_PATH_MODULE_Paytm  .'custom_callbackurl', $storeId);
                $callbackUrl   = rtrim($this->_urlInterface->getUrl('paytm/processing/response',array('_nosid'=>true)),'/');

				$website = $this->getConfigValue(self::XML_PATH_MODULE_Paytm  .'Website', $storeId);

				$_totalData = $order->getData();
				$email = $_totalData['customer_email'];

				$customer =  $this->_customerFactory->create();
				$customer->setWebsiteId($websiteId);
				$customer->loadByEmail($email); 

				$telephone = $order->getBillingAddress()->getTelephone();
				//create array using which checksum is calculated
				
				$data = 	array(
					'MID' =>	$merid,  				
					'TXN_AMOUNT' =>	$price,
					'CHANNEL_ID' => "WEB",
					'INDUSTRY_TYPE_ID' => $industry_type,
					'WEBSITE' => $website,
					'CUST_ID' => $customer->getId(),
					'ORDER_ID' => $orderId,   				    
					'EMAIL'=> $email,
					'MOBILE_NO' => preg_replace('#[^0-9]{0,13}#is','',$telephone)
				);

				$data['CALLBACK_URL'] = $this->_urlInterface->getUrl('externallink/index/response');

				//generate customer id in case this is a guest checkout
				if(empty($data['CUST_ID'])){
					$data['CUST_ID'] = $email;
				}

				$checksum = $this->_paytmhelper->getChecksumFromArray($data, $mer); //generate checksum
				$data['CHECKSUMHASH'] = $checksum;
				$params['status'] = 1;
				$params['data'] = $data;
				//~ echo "<pre>"; print_r($params); die;
				return $params;
				
			}
			else{
				$params['status'] = 0;
				return $params;
			}
		}
		catch(\Exception $ex){ 
			print_r($ex->getMessage());
			echo "<div style='width:100%; text-align:center;'><b>Something went wrong.<b></div>"; die;
		}
		   }

    
    /**
     * Getting gateway ur
     */
    public function getFormAction()
    {
       $storeId   = $this->storeManager->getStore()->getId();
       //echo '==='.$const = $this->getConfigValue(self::XML_PATH_MODULE_GlobalKey  .'key', $storeId);
       $const =  1;

       return $this->_paytmhelper->decrypt_e($this->getConfigValue(self::XML_PATH_MODULE_Paytm  .'transaction_url', $storeId),$const);
    }
      

}