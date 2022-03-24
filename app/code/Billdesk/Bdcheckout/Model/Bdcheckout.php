<?php


namespace Billdesk\Bdcheckout\Model;

use Magento\Sales\Api\Data\TransactionInterface;

class Bdcheckout extends \Magento\Payment\Model\Method\AbstractMethod {

    const PAYMENT_BDCHECKOUT_CODE = 'bdcheckout';
   

    protected $_code = self::PAYMENT_BDCHECKOUT_CODE;

    /**
     *
     * @var \Magento\Framework\UrlInterface 
     */
    protected $_urlBuilder;
    
    private $checkoutSession;

    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
      public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Billdesk\Bdcheckout\Helper\Bdcheckout $helper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Checkout\Model\Session $checkoutSession      
              
    ) {
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;
        $this->checkoutSession = $checkoutSession;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

    }

    /*public function canUseForCurrency($currencyCode) {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }*/

     protected $_checksum = null;

    protected $_globalMap = array(
        // commands
        'merchantIdentifier' => '',
        'orderId' => '',
        'returnUrl' => '',
        'buyerEmail' => '',
        'buyerFirstName' => '',
        'buyerLastName' => '',
        'buyerAddress' => '',
        'buyerCity' => '',
        'buyerState' => '',
        'buyerCountry' => '',
        'buyerPincode' => '',
        'buyerPhoneNumber' => '',
        'txnType' => '1',
        'zpPayOption' => '1',
        'mode' => '1',
        'currency' => 'USD',
        'amount' => '0',
        'merchantIpAddress' => '',
        'purpose' => '',
        'productDescription' => '',
        'product1Description' => '',
        'product2Description' => '',
        'product3Description' => '',
        'product4Description' => '',
        'shipToAddress' => '',
        'shipToCity' => '',
        'shipToState' => '',
        'shipToCountry' => '',
        'shipToPincode' => '',
        'shipToPhoneNumber' => '',
        'shipToFirstname' => '',
        'shipToLastname' => '',
        'txnDate' => '',
    );

    protected $_mandatory = array(
        'merchantIdentifier',
        'orderId',
        'buyerEmail',
        'buyerFirstName',
        'buyerLastName',
        'buyerAddress',
        'buyerCity',
        'buyerState',
        'buyerCountry',
        'buyerPincode',
        'buyerPhoneNumber',
        'txnType',
        'zpPayOption',
        'mode',
        'currency',
        'amount',
        'purpose',
        'merchantIpAddress',
        'productDescription',
        'txnDate'
    );

    public function getRedirectUrl() {
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    public function getReturnUrl() {
        return $this->helper->getUrl($this->getConfigData('return_url'));
    }

    public function getNotifyUrl() {
        return $this->helper->getUrl($this->getConfigData('notify_url'));
    }

    /**
     * Return url according to environment
     * @return string
     */
    public function getCgiUrl() {
        $env = $this->getConfigData('environment');
        if ($env === 'prod') {
            return $this->getConfigData('prod_url');
        }
        return $this->getConfigData('test_url');
    }

    public function buildCheckoutRequest() {
       
        //$params = array();

        $fields = $this->_globalMap;  
        $order = $this->checkoutSession->getLastRealOrder();
        $billing_address = $order->getBillingAddress();
        $currency_code = $order->getOrderCurrencyCode();      
        $order_data = $order->getData();       
        $shippingAddress = $this->getShippingAddress();
        $amount = $this->_convertAmount($order->getGrandTotal(), $order->getOrderCurrencyCode());
        $currency = $order->getOrderCurrencyCode();
       
        $buyerPhoneNumber = $billingAddress->getTelephone();
        $buyerEmail     = $order->getCustomerEmail();
        $returnUrl      = $this->getReturnUrl();
        $notpresent     ="NA";
        $securityType   ="R";
        $settlementType ="F";


        $fields = Array(
                'merchantId' => $this->getConfigData("merchant_id"),
                'orderId' => $order->getIncrementId(),
                'merchantUserRefNol' => $notpresent,
                'amount' => $amount, //Amount should be in rs ps
                'bankId' => $notpresent,
                'meBankId' => $notpresent,
                'txnType' => $notpresent,
                'currency' => $order->getOrderCurrencyCode(),
                'itemCode' => $notpresent,
                'securityType' => $securityType,
                'securityId' => strtolower($this->getConfigData("merchant_id")),
                'txnDate' => $notpresent,
                'notpresent' => $notpresent,
                'settlementType' => $settlementType,
                'additionalInfo1' => isset($buyerPhoneNumber)?$buyerPhoneNumber:$notpresent,
                'additionalInfo2' => isset($buyerEmail)?$buyerEmail:$notpresent,
                'additionalInfo3' => $notpresent,
                'additionalInfo4' => $notpresent,
                'additionalInfo5' => $notpresent,
                'additionalInfo6' => $notpresent,
                'additionalInfo7' => $notpresent,
                "returnUrl" => $returnUrl
        );

       return $fields; 
    }

    public function generateBDSignature($params) {
		//~ echo "<pre>"; print_r($params); 
        $secretKey = $this->getConfigData("salt");
        ksort($params);
        //~ echo "<pre>"; print_r($params); 
        $signatureData = "";
        $countP =  count($params);
        $count = 1;
        foreach ($params as $key => $value){
           $signatureData .= $key."=".$value;
           if($count < $countP){
			   $signatureData .= "~";
		   }
		   $count++;
        }
        $signatureData .= $secretKey;
        //~ echo "signature data :: ".$signatureData."<br>";
        $signature = hash("sha256", $signatureData);
        return strtoupper($signature);
    }

    //validate response
    public function validateResponse($returnParams) {
		  $computedSignature = "";
		  $resArr = array();
		  $signature = $returnParams["HASH"];
          foreach($returnParams as $key => $val){
			if($key == "HASH"){
				continue;
			}
			$resArr[$key] = $val;
		  }
		  //~ echo "<pre>"; print_r($resArr); 
		  $computedSignature = $this->generateLPSignature($resArr);
		  //~ echo "computedSignature :: ".$computedSignature."<br>";
		  //~ echo "signature :: ".$computedSignature."<br>";
		  //~ die("reach");
          if ($computedSignature != $signature) {
            return "009";
          }

          return $returnParams["RESPONSE_CODE"];

    }

    public function postProcessing(\Magento\Sales\Model\Order $order, \Magento\Framework\DataObject $payment, $response) {
        
        $payment->setTransactionId($response['PG_REF_NUM']);
        $payment->setTransactionAdditionalInfo('Transaction Message', $response['RESPONSE_MESSAGE']);
        $payment->setAdditionalInformation('billdesk_payment_status', 'approved');
        $payment->addTransaction(TransactionInterface::TYPE_ORDER);
        $payment->setIsTransactionClosed(0);
        $payment->place();
        $order->setStatus('processing');
        $order->save();
    }
    
    public function getSupportedCurrency(){
		return array('INR','GBP','USD','EUR');
	}
	
	public function getCurrencyNumericCode($currency_code){
		
		$supportedCurrency = array('INR' => 356,'GBP' => 826,'USD' => 840,'EUR' => 978);
		return $supportedCurrency[$currency_code];
		
	}

}
