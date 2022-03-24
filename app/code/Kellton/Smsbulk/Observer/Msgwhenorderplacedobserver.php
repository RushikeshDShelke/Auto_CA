<?php
namespace Kellton\Smsbulk\Observer;

class Msgwhenorderplacedobserver implements \Magento\Framework\Event\ObserverInterface
{

	 protected $_customer;     
     protected $_order;
     protected $_product;     
     protected $date;

    public function __construct(
      \Magento\Catalog\Block\Product\Context $context,      
      \Magento\Customer\Api\CustomerRepositoryInterface $customer,
      \Magento\Sales\Api\Data\OrderInterface $order,
      \Magento\Catalog\Model\ProductFactory $product,      
      \Magento\Framework\Stdlib\DateTime\DateTime $date
       ) {

       $this->_customer         =  $customer;             
       $this->_order            =  $order;
       $this->_product          =  $product;       
       $this->date              =  $date;
    }


  public function execute(\Magento\Framework\Event\Observer $observer)
  {

  	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

  	    $smsbulkModel = $objectManager->create('Kellton\Smsbulk\Model\Smsbulk');

		$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$storeId       = $storeManager->getStore()->getStoreId();
		$currentdate   = $this->date->gmtDate();
		$currentdate = date('Y-m-d H:i:s',strtotime($currentdate.'+330 minutes', 0));  

  	    $orders  = $observer->getEvent()->getOrderIds();
        $order   = $this->_order->load($orders['0']);
        $crtdate = date('d-m-Y H:i:s') . ' ordstatus=' . $order->getStatus();
        
        if ($order->getStatus() == 'processing') {
			if($order->getHasChild())
			{
				$childOrderIds = explode(',',$order->getChildIds());
				foreach($childOrderIds as $childOrderId)
				{
					
					$order = $this->_order->loadByIncrementId($childOrderId);
	
					//Send email on order confirmation code starts
					//$order->sendNewOrderEmail(true);
					//Send email on order confirmation code ends
					$orderdate   = $order->getCreatedAt();
					$ordernumber = $order->getIncrementId();
					$mobile      = $order->getShippingAddress()->getTelephone();
					$username    = $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname();
					$smstitle    = 'Order Confirmation to customer';
					$smstitle1   = 'Order Confirmation to craftman';
					/**send sms to customer start**/
					$msg         = 'Dear ' . $username . ',Thank you for shopping at CraftMaestros.com.Your order is confirmed. Order number ' .$ordernumber . ' For more details: Login to your account on craftmaestros.com. With appreciation, Craft Maestros';

					$path        = "http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=" . $mobile . "&Text=" . urlencode($msg);
					$ch          = curl_init($path);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$output = curl_exec($ch);
					curl_close($ch);

					/**send sms to Customer Login **/					
					
					$items        = $order->getAllItems();
					$venderemails = array();
					foreach ($items as $item) {
						$product = $this->_product->create()->load($item->getProductId());

						if ($product->getData('creator_id') != null) {
							$vendor_id            = $product->getData('creator_id');					
							//$vendor               = $this->_customer->getById($vendor_id);
							$vendor  = $objectManager->create('Magento\Customer\Model\Customer')->load($vendor_id);
							//$mobile1              = $order->getShippingAddress()->getTelephone();
							$mobile1 =  $vendor['mobile_number'];
							$customerFullName     = $vendor->getFirstname() . ' ' . $vendor->getLastname();
							//$supplierCustomFeilds = unserialize($vendor->getCustomFieldsValues());
							$merchantId           = "MCID";
							 //foreach ($supplierCustomFeilds as $customFieldsValue) {
							 //	if ('merchant_id' === $customFieldsValue['name']) {
							 //		$merchantId = $customFieldsValue['value'];
								//}
							 //}
							$artisannumber = $merchantId . '-' . $ordernumber;
							if ($mobile1) {
								if (!in_array($vendor_id, $venderemails)) {
									$venderemails[] = $vendor_id;
									/**send sms to vendor start**/
									$msg1           = 'Dear Mr/ MS ' . $customerFullName . ',You have an order from CraftMaestros.com.Artisan Order number : ' . $artisannumber . ' For more details : Login to your artisan account on craftMaestros.com/supplier/login and view the details.With appreciation,Team Craft Maestros';

									$path1          = "http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=" . $mobile1 . "&Text=" . urlencode($msg1);
									$ch1            = curl_init($path1);
									curl_setopt($ch1, CURLOPT_HEADER, 0);
									curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
									$output = curl_exec($ch1);
									curl_close($ch1);									
                                    
									$smsbulkModel->setMessdate($currentdate)->setMesscont($msg1)->setName($smstitle1)->save();


									/**send sms to vendor start end**/
									
								}
							}
						}
					}
					

					// try {
					// 	$smsbulkModel->setMessdate($currentdate)->setMesscont($msg1)->setName($smstitle1)->save();
					// }
					// catch (Exception $e) {
					// 	echo $e;
					// }

				}
			}
			else{
            
				//Send email on order confirmation code starts
								
				$orderdate   = $order->getCreatedAt();
				$ordernumber = $order->getIncrementId();
				$mobile      = $order->getShippingAddress()->getTelephone();
				$username    = $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname();
				$smstitle    = 'Order Confirmation to customer';
				$smstitle1   = 'Order Confirmation to craftman';
				/**send sms to customer start**/
				$msg         = 'Dear ' . $username . ', Thank you for shopping at CraftMaestros.com.Your order is confirmed.Order number ' . $ordernumber . ' For more details: Login to your account on craftmaestros.com.With appreciation, Craft Maestros';

				$path        = "http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=" . $mobile . "&Text=" . urlencode($msg);
				$ch          = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$output = curl_exec($ch);
				curl_close($ch);
				/**send sms to customer start end**/				
				
				$items        = $order->getAllItems();
				$venderemails = array();
				foreach ($items as $item) {
					//$product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
					$product = $this->_product->create()->load($item->getProductId());

					if ($product->getData('creator_id') != null) {
						$vendor_id            = $product->getData('creator_id');						
						
						//$vendor = $this->_customer->getById($vendor_id);
						$vendor  = $objectManager->create('Magento\Customer\Model\Customer')->load($vendor_id);
						//$mobile1              = $order->getShippingAddress()->getTelephone();
						$mobile1 =  $vendor['mobile_number'];
												
						$customerFullName     = $vendor->getFirstname() . ' ' . $vendor->getLastname();
						//$supplierCustomFeilds = unserialize($vendor->getCustomFieldsValues());
						$merchantId           = "MCID";
						// foreach ($supplierCustomFeilds as $customFieldsValue) {
						// 	if ('merchant_id' === $customFieldsValue['name']) {
						 //		$merchantId = $customFieldsValue['value'];
						 //	}
						 //}

						$artisannumber = $merchantId . '-' . $ordernumber;
						if ($mobile1) {
							if (!in_array($vendor_id, $venderemails)) {
								$venderemails[] = $vendor_id;
								/**send sms to vendor start**/
								$msg1           = 'Dear Mr/ MS ' . $customerFullName . ',You have an order from CraftMaestros.com.Artisan Order number : ' . $artisannumber . '	For more details : Login to your artisan account on craftMaestros.com/supplier/login and view the details.With appreciation,Team Craft Maestros';
								
								$path1          = "http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=" . $mobile1 . "&Text=" . urlencode($msg1);
								$ch1            = curl_init($path1);
								curl_setopt($ch1, CURLOPT_HEADER, 0);
								curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
								$output = curl_exec($ch1);
								curl_close($ch1);

								
								$smsbulkModel->setMessdate($currentdate)->setMesscont($msg1)->setName($smstitle1)->save();
								
								/**send sms to vendor start end**/
								
							}
						}
					}
				}
				
				
				// try {
				// 	$smsbulkModel->setMessdate($currentdate)->setMesscont($msg1)->setName($smstitle1)->save();
				// }
				// catch (Exception $e) {
				// 	echo $e;
				// }
			}
			
        }

     return $this;
  }

}
