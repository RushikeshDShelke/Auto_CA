<?php
namespace Kellton\Shipmentemail\Observer;

class Salesordershipmentobserver implements \Magento\Framework\Event\ObserverInterface
{

 public function execute(\Magento\Framework\Event\Observer $observer)

  {     
  	             date_default_timezone_set('Asia/Kolkata'); 
  	             $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			     $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
				 $storeId       = $storeManager->getStore()->getStoreId();
				 $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;

				 $crtdate=date('d-m-Y H:i:s');                			
				/* @var $shipment Mage_Sales_Model_Order_Shipment */
				 $shipment = $observer->getEvent()->getShipment();
				 $orders = $shipment->getOrder();
				 $orderId = $orders->getId();
				 $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
				
				 $shipid=$shipment->getId();				

				 $configValue = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('supplierfrontendproductuploader_catalog/supplierfrontendproductuploader_presentation/shipment_emaild', $storeScope);

				 $data = array('orderid'=>$orderId,'shipmentid'=>$shipid,'createdat'=>$crtdate,'delay'=>$configValue,'status'=>0);
				 $shipmentdelay = $objectManager->create('Kellton\Shipmentdelay\Model\Shipmentdelay')->setData($data);
				 $shipmentdelay->save();
				 
				 
				 $shipments = $objectManager->create('Magento\Sales\Model\Order\Shipment')->loadByIncrementId($shipid);
				//  $orders = $shipments->getOrder();
				 // $orderId = $orders->getId();
				 // $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
				 $incrementid = $order->getIncrementId();
				 $mobile=$order->getShippingAddress()->getTelephone();
				 $username= $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
				//  print_r($username);die;
				$shippingAddress = $order->getShippingAddress();
				$custemail=$shippingAddress->getEmail();
				$custname=$shippingAddress->getName();
				$street = $order->getShippingAddress()->getStreetFull();
				$city = $order->getShippingAddress()->getCity();
				$postcode = $order->getShippingAddress()->getPostcode();
				$state = $order->getShippingAddress()->getRegion();
				$getCountryId = $order->getShippingAddress()->getCountryId();
				$orderdate=$order->getCreatedAt();
				
				// Transactional Email Template's ID
				$templateId = 5;
				// Set sender information           

				$senderName = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('trans_email/ident_support/name', $storeScope);

				$senderEmail = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('trans_email/ident_support/email', $storeScope);


				$sender = array('name' => $senderName,
							'email' => $senderEmail);      
				
				$productnamecoll='';
				$itemsCollection = $shipments->getItemsCollection();
					foreach($itemsCollection as $item){
					   $productid=$item->getProductId();	
					   $_product = $item->getProduct();
					   $productname= $item->getName();
						if($productnamecoll==''){
							$productnamecoll.= $productname;
						}else{
							$productnamecoll.= ', '. $productname;
						}
						$productQty= intval($item->getQty());

						$product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());


						$productimage= '<img src="'.$product->getImageUrl().'" height="200px" />';
						$supplier_id = $product->getCreator_id();

						$vendor = $objectManager->create('Magento\Customer\Model\Customer')->load($supplier_id);

						$supplierName = $vendor->getFirstname() .' '. $vendor->getLastname();
						$recepientEmail=$vendor->getEmail();
						//$recepientEmail='shailendra1610@gmail.com';
						// Set variables that can be used in email template
						$vars = array('id' => $productid,'productname' => $productname,'orderno' => $incrementid,'productQty' => $productQty,'productimage' => $productimage,'orderdate' => $orderdate,'supplierName' => $supplierName,'custname' => $custname,'custemail' => $custemail,'street' => $street,'city' => $city,'postcode' => $postcode,'state' => $state,'getCountryId' => $getCountryId);								
						
						$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());

						$transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
										->setTemplateOptions($templateOptions)
										->setTemplateVars($vars)
										->setFrom($sender)
										->addTo($recepientEmail)
										->getTransport();
						$transport->sendMessage();
						$this->inlineTranslation->resume();

					}

					$msg='Dear '.$username.', 

					Thank you for purchasing your remarkable item at Craftmaestros.com. 
					Your item is ready to be picked from our Master Artisan.

					Order number '.$incrementid.'

					Product Name :'.$productnamecoll.'

					With appreciation
					Craft Maestros ';
    //             $path="http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=".$mobile."&Text=".urlencode($msg);
				// $ch = curl_init($path);
				// curl_setopt($ch, CURLOPT_HEADER, 0);
				// curl_exec($ch);
				// curl_close($ch);

     return $this;
  }
}