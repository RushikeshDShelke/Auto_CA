<?php

namespace DotTrack\Trackorder\Controller\Order;

use Magento\Framework\Controller\ResultFactory;

class Shipment extends \Magento\Framework\App\Action\Action
{
   protected $resultPageFactory;
   protected $_orderCollectionFactory;
   protected $_order;
   protected $registry = null;  
   protected $helperData;

  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface, 
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Registry $registry,       
        \DotTrack\Trackorder\Helper\Data $helperData      
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_order = $order;
        $this->registry = $registry;       
        $this->helperData = $helperData;

        parent::__construct($context);
    }

     public function initOrder($post) {
        if ($post) {
             $orderId = $post["order_id"];
             $email = $post["customer_email"];
            //exit;
            $order = $this->_order->loadByIncrementId($orderId);
            $cEmail = $order->getCustomerEmail();
            if ($cEmail == trim($email)) {     
            $this->registry->register('current_order', $order);                
              
            } else {    
                $this->registry->register('current_order', $this->_order);              
            }
        }
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        if ($post) 
        {
            try 
            {           

            $this->initOrder($post);
            $order = $this->registry->registry('current_order'); 


            if ($order->getId()) 
            {
                $shippingCarrier="";
                $trackingNumber = 0;
                $shipments = $order->getShipmentsCollection();  
                $shipped_table= '';
                if(count($shipments->getData()) == 0){
                    if($order->getState()=='processing'){
                           // echo count($order->getAllItems()); exit;

                        if(count($order->getAllItems())<=1)
                        {
                          $shipmentFlag = ' active'; 
                            $shipped_table = "<div class='order-tracking-container'>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Processing</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item".$shipmentFlag."'><span></span>Ready for shipment</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item'><span></span>In-Transit</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item'><span></span>Delivered</div>
                                </div>
                            </div>";
                         
                        }
                        else{
                            $shipped_table = "<table class='order-item-tracking-table'><center><caption>Order Item Tracking</caption></center><tr><th>Product Name</th><th>Product SKU</th><th>Qty</th><th>Item Status</th></tr>";
                            foreach($order->getAllItems() as $item)
                            {
                                $shipped_table.="<tr><td>".$item->getName()."</td><td>".$item->getSku()."</td><td>".$item->getQtyOrdered()."</td>";
                                if($item->getOrderitemstatus())
                                {
                                    $shipped_table.="<td>Ready for shipment</td>";
                                    $shipmentFlag = ' active';
                                }
                                else{
                                    $shipped_table.="<td>Not ready for shipment</td>";
                                    $shipmentFlag = '';
                                }
                                $shipped_table.="</tr>";
                            }
                            $shipped_table.="</table>";

                        }
                           
                    }
                    else {
                        $error_messagenew = __('Shipment is not created for this order.');
                        $responsenew =  array('status' => false, 'emessage' => $error_messagenew); 
                        $resultJsonnew = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJsonnew->setData($responsenew);
                        return $resultJsonnew;
                    }                        
                }
                if($order->getState()=='complete') {
                        $shipmentFlag = ' active'; 
                        $shipped_table = "<div class='order-tracking-container'>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Processing</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Ready for shipment</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>In-Transit</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Delivered</div>
                                </div>
                            </div>";
                }

                foreach($shipments as $shipment){
                        $tracks = $shipment->getTracksCollection();
                        foreach ($tracks as $track){
                            $trackingInfos = $track->getData();
                            $shippingCarrier=$trackingInfos["carrier_code"];
                            $trackingNumber=$trackingInfos["track_number"];
                        }
                }
                       
                //Delhivery
                if($shippingCarrier == "delhivery")
                {
                    $delhivery = $this->helperData->getTrackorderStatus($order->getId());

                    if($delhivery[0]["status"] == 'Delivered')
                    {
                        $shipped_table = "<div class='order-tracking-container'>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Processing</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Ready for shipment</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>In-Transit</div>
                                </div>
                                <div class='order-processing'>
                                    <div class='process-item active'><span></span>Delivered</div>
                                </div>
                            </div>";
                     }
                     else{
                        $shipped_table = "<div class='order-tracking-container'>
                        <div class='order-processing'>
                            <div class='process-item active'><span></span>Processing</div>
                        </div>
                        <div class='order-processing'>
                            <div class='process-item active'><span></span>Ready for shipment</div>
                        </div>
                        <div class='order-processing'>
                            <div class='process-item active'><span></span>In-Transit</div>
                        </div>
                        <div class='order-processing'>
                            <div class='process-item'><span></span>Delivered</div>
                        </div>
                    </div>";
                     }
                     $shipped_table .= "<div class='track-source'>   <span>AWB Number : ".$trackingNumber."</span><span>Shipment Status : ".$delhivery[0]["status"]." </span></div>";
                }
                //DHL
                if($shippingCarrier == "dhlexpress")
                {
                    $shipped_table = "<div class='track-source'>   <span>AWB Number : ".$trackingNumber." </span><span><a class='button' href='http://www.dhl.co.in/en/express/tracking.html?AWB=".$trackingNumber."&brand=DHL' target='_blank'>Click here to track</a></span></div>";
                } 
                //Dotzot 
                if($shippingCarrier == "custom")
                {
                     $shipped_table = "   AWB Number : ".$trackingNumber." <br><a href='https://instacom.dotzot.in/GUI/Tracking/Track.aspx' target='_blank'>Click here to track</a>";
                }
                /** else{
                   $shipped_table = "<div class='order-tracking-container'>
                        <div class='order-processing'>
                            <div class='process-item active'><span></span>Processing</div>
                        </div>
                        <div class='order-processing'>
                            <div class='process-item active'><span></span>Ready for shipment</div>
                        </div>
                        <div class='order-processing'>
                            <div class='process-item active'><span></span>In-Transit</div>
                        </div>
                        <div class='order-processing'>
                            <div class='process-item'><span></span>Delivered</div>
                        </div>
                    </div>";
                }
                */

                $response  = array('trackData' => $shipped_table, 'status' => true);                 
                //echo '<pre>'; print_r($response); echo '</pre>'; exit;
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                 
                $resultJson->setData($response);
                 
                return $resultJson;  

               }                   
            
            }
            catch (Exception $e) 
            {
                //$this->_coreSession->addError($e);
                 $error_messagenew = __('Do not leave blanks Order Id and Email.');
                 $responsenew =  array('status' => false, 'emessage' => $error_messagenew); 
                 $resultJsonnew = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                 $resultJsonnew->setData($responsenew);
                 return $resultJsonnew;
                            
            }
        } 
        else 
        {
            $this->_redirect('*/*/');
            return;
        }
    }

}
