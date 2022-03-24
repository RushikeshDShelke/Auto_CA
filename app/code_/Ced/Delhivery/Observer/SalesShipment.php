<?php
/**
 * kellton
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * 
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Delhivery\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class SalesShipment implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    private $messageManager;
    protected $request;
    protected $scope;

   public function __construct (        
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        ManagerInterface $messageManager
    ) {
        $this->_objectManager=$objectManager; 
        $this->_request = $request;
        $this->scope=$scope;
        $this->messageManager = $messageManager;
    }
 

    /**
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {  	
$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test13may.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
	
        $postData = $this->_request->getPostValue();
        $invoice = $observer->getEvent()->getInvoice();
        $shipment = $observer->getEvent()->getShipment();   
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if(isset($postData['invoice']) && count($postData['invoice']))
            $postItems = $postData['invoice']['items'];
        else
            $postItems = $postData['shipment']['items'];
        $flag = false;
        $awbno = 0;

        if(isset($postData['tracking']) && count($postData['tracking'])>0 && $postData['tracking'][1]['carrier_code']=='delhivery'){
            $flag = true;
            $awbno = $postData['tracking'][1]['number'];
        }
       
        if(strpos($shippingMethod, 'delhivery') !== false || $flag)
        {
            $url =  $this->scope->getValue('carriers/delhivery/gateway_url');
            $token = $this->scope->getValue('carriers/delhivery/license_key');
            $clientid = $this->scope->getValue('carriers/delhivery/client_id');
            if(!$clientid || !$token || !$url)
            {
                $this->messageManager->addErrorMessage(__('ClientId , Key & Gateway Url can not be empty.'));
                throw new \Exception();
            }
            //$token = "$token"; // replace this with your token key
            $url .= "api/cmu/create.json";

            if(!$awbno){
                $modelnew = $this->_objectManager->create('\Ced\Delhivery\Model\Awb')->getCollection()->addFieldToFilter('state',array('eq'=>2))->getData();

                foreach($modelnew as $models)
                {
                    $awbno=$models['awb'];
                    break;
                }
            }
            
            if($awbno != NULL)
            { 
                $address =$this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($order['shipping_address_id']);
                $items = $this->_objectManager->get("Magento\Sales\Model\Order\Item")->getCollection()->addFieldToFilter('order_id',$order->getEntityId());
                $item_detail = '';
                $orderTotal = 0;
                $quantity = 0;              
                foreach($items as $itemId)
                {
                    
                    if(isset($postItems[$itemId->getItemId()])){
                        $qty = $postItems[$itemId->getItemId()];
                        $item_detail .= $item_detail ? ' , '.$itemId->getName() : $itemId->getName();
                        $orderTotal = $orderTotal + $qty * $this->_objectManager->create('Ced\Delhivery\Helper\Data')->getItemAmount($itemId); 
                        $quantity = $quantity +  $qty;
                    }   
                }
                
                if(!count($order->getShipmentsCollection())){
                    $orderTotal += $order->getShippingAmount();
                } 

                $methodcode = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' || 'checkmo') ? "COD" :"Pre-Paid";
                $codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' || 'checkmo') ? $orderTotal : "00.00";
                /////////////start: building the package feed/////////////////////
                $abc = date('c');
                $shipment = array();
                $shipment['client'] =$clientid;
                $shipment['waybill'] = $awbno;
                $shipment['name'] = $address->getFirstname().' '.$address->getLastname(); // consignee name
                $shipment['order'] = $order->getIncrementId() ;
                $shipment['products_desc'] = $item_detail;
                $shipment['order_date'] = $order['updated_at'];
                $shipment['payment_mode'] = $methodcode;
                $shipment['total_amount'] = $orderTotal; // in INR
                $shipment['cod_amount'] = $codamount;  // amount to be collected, required for COD
                $shipment['add'] = $address['street']; // consignee address
                $shipment['city'] = $address->getCity();
                
                if($address->getRegion())
                    $shipment['state'] = $address->getRegion();             
                $shipment['country'] = 'India';
                if($address->getTelephone())
                    $shipment['phone'] = $address->getTelephone();
                $shipment['pin'] = $address->getPostcode();
                $shipment['return_add'] = $this->scope->getValue('shipping/origin/street_line1')
                                            .' '.$this->scope->getValue('shipping/origin/street_line2');
                $shipment['return_city'] = $this->scope->getValue('shipping/origin/city');
                $shipment['return_country'] = 'India';//$country;
                $shipment['return_name'] = $clientid; // Use client warehouse name $clientid;
                $shipment['return_phone'] = $this->scope->getValue('general/store_information/phone');
                $shipment['return_pin'] = $this->scope->getValue('shipping/origin/postcode');
                $shipment['return_state'] = $this->_objectManager->create('Magento\Directory\Model\Region')
                                              ->load($this->scope->getValue('shipping/origin/region_id'))->getName();
                $shipment['supplier'] = $clientid;
                $shipment['quantity'] = $quantity;
                
                
                $pickup_location['add'] = $this->scope->getValue('shipping/origin/street_line1').' '.$this->scope->getValue('shipping/origin/street_line2');
                $pickup_location['city'] = $this->scope->getValue('shipping/origin/city');
                
                $pickup_location['country'] ='India';
                $pickup_location['name'] = "AMMARA SURFACE";

                $pickup_location['phone'] = $this->scope->getValue('general/store_information/phone');
                $pickup_location['pin'] = $this->scope->getValue('shipping/origin/postcode');
                $fromRegion = $this->scope->getValue('shipping/origin/region_id');


                if(is_numeric($fromRegion))
                    $fromRegion = $this->_objectManager->create('Magento\Directory\Model\Region')->load($this->scope->getValue('shipping/origin/region_id'))->getName();

                $pickup_location['state'] = $fromRegion;

                if(!$pickup_location['phone'] || !$pickup_location['pin'] || !trim($pickup_location['add']) || !$pickup_location['city'])
                {
                    $errorMessage = __('These details must be filled for Delhivery Shipment Creation. Stores->Configuration->Sales->Shipping Setting & Stores->Configuration->General->Store Information');
                    $this->_objectManager->get('Magento\Framework\Message\ManagerInterface')->addWarningMessage($errorMessage);
                    $url = $this->_objectManager->get('Magento\Framework\App\Response\RedirectInterface')->getRedirectUrl();

                    $this->_objectManager->get('Magento\Framework\App\ResponseFactory')->create()->setRedirect($url)->sendResponse();
                    throw new \Exception($errorMessage); 
                }

                $package_data['pickup_location'] = $pickup_location;
                $shipments = array($shipment);
                $package_data['shipments'] = $shipments;
                $params['format'] = 'json';
                $params['data'] =json_encode($package_data);
                
                /////////////end: building the package feed/////////////////////
		$logger->info($url);
                $result = $this->_objectManager->create('Ced\Delhivery\Helper\Data')->Executecurl($url,'POST',$params);
		$logger->info($result);

                $decode_response = json_decode($result, true);

                if(!$flag){   
                    if($decode_response['success'])
                    {
                        $shipment = $observer->getEvent()->getShipment();
                        $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                        ->setNumber($awbno)
                        ->setCarrierCode('delhivery')
                        ->setTitle('Delhivery');
                        $shipment->addTrack($track);
                    }else{              

                        $error = (isset($decode_response['packages'][0]['remarks']) && !is_array($decode_response['packages'][0]['remarks'])) ? $decode_response['packages'][0]['remarks'] : $decode_response['packages'][0]['remarks'][0];
                        $this->messageManager->addErrorMessage(__($error));
                        throw new \Exception($error);
                    }   
                }
                if($flag && !$decode_response['success']){
                    $error = (isset($decode_response['packages'][0]['remarks']) && !is_array($decode_response['packages'][0]['remarks'])) ? $decode_response['packages'][0]['remarks'] : $decode_response['packages'][0]['remarks'][0];
                    $this->messageManager->addErrorMessage(__($error));
                    throw new \Exception($error);
                }   
            }
        }
    }
   

}
