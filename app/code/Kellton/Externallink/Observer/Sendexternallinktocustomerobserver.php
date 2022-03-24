<?php
namespace Kellton\Externallink\Observer;

class Sendexternallinktocustomerobserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $dataHelper;

    public function __construct(        
        \Kellton\Externallink\Helper\Data $dataHelper       
    ) {       
        $this->dataHelper = $dataHelper;       
    }


  public function execute(\Magento\Framework\Event\Observer $observer)
  {
     
  	//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
  	//$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
	//$storeId       = $storeManager->getStore()->getStoreId();
	//$storeId       = $this->dataHelper->getStoreId();

	//$objDate       = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');

	$objDate       =  $this->dataHelper->getDatetime();

  	$order         = $observer->getEvent()->getOrder();
  	$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();

    if($payment_method_code == "externallink"){
			$customer_id = $order->getCustomerId();
			
			//$customerData = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
			$customerData = $this->dataHelper->getCustomerById($customer_id);

			if($customerData->getId()){

				$getpaymenturl = $this->dataHelper->geBaseUrl().$payment_method_code.'?id='.base64_encode($order->getIncrementId());
				$mobile = $customerData->getPhoneNumber();
				$smstitle='Send payment link to customer';
				/**send sms to customer start**/
				$msg='Dear '.$customerData->getName().', 

				Thank you for shopping at CraftMaestros.com. 
				Your order is confirmed.
				Order number '.$order->getIncrementId().'

				For doing payment: Click here '.$getpaymenturl.'.

				With appreciation, 
				Craft Maestros';
				$path="http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=".$mobile."&Text=".urlencode($msg);
				//~ echo "path :: ".$path; die("reach");
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$output = curl_exec($ch);
				curl_close($ch);

				$this->dataHelper->getsms()->setMessdate($objDate->gmtDate())->setMesscont($msg)->setName($smstitle)->save();

				/**send sms to customer start end**/
			}
		}

     return $this;
  }
}