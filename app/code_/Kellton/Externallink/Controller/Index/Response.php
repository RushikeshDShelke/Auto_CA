<?php

namespace Kellton\Externallink\Controller\Index;

class Response extends \Magento\Framework\App\Action\Action
{

	 // Checking POST variables.

	protected $request;

    public function __construct(
       \Magento\Framework\App\RequestInterface $request,  \Magento\Framework\ObjectManagerInterface $objectManager        
    ) {
       $this->request = $request;
       $this->_objectManager = $objectManager;
       
    }
   

    protected function _checkReturnedPost()
    {
        // check request type
        if (!$this->request->getPostValue())           
        throw new \Magento\Framework\Exception\LocalizedException(__( 'Wrong request type.'));

        // get request variables
        $request = $this->request->getPostValue();

        if (empty($request))
        	throw new \Magento\Framework\Exception\LocalizedException(__( 'Request does not contain POST elements.'));

        // load order for further validation
        $order =$this->_objectManager->get('\Magento\Sales\Model\Order')->loadByIncrementId($request['ORDERID']);

        // check order id
        $orderId = $request['ORDERID'];
        if(stripos($orderId, "_"))
        {
          $orderIdArray = explode("_",$orderId);
          $order =$this->_objectManager->get('\Magento\Sales\Model\Order')->loadByIncrementId($orderIdArray[0]);
          
        }
        if (empty($request['ORDERID']) )
        	throw new \Magento\Framework\Exception\LocalizedException(__( 'Missing or invalid order ID'));
                   
        if (!$order->getId())           
        throw new \Magento\Framework\Exception\LocalizedException(__( 'Order not found'));


        return $request;
    }
    

    public function execute()
    {

      echo 'response111111111111111'; exit;

    	$request = $this->_checkReturnedPost();
      //~ echo "<pre>"; print_r($request);
      try{
     
      $order =$this->_objectManager->get('\Magento\Sales\Model\Order');
      $orderId = $request['ORDERID'];
      if(stripos($orderId, "_"))
      {
        $orderIdArray = explode("_",$orderId);
      }
		  $order->loadByIncrementId($orderIdArray[0]);
		  if($request['STATUS'] == 'TXN_FAILURE'){
			if (!$order->getId()) {
				echo "<div style='width:100%; text-align:center;'><b>No order for processing found.<b></div>"; die;
			}

			$order->setState(\Magento\Sales\Model\Order::STATE_CANCELED,true)->save();

			echo "<div style='width:100%; text-align:center;'><b>The order has been canceled.<b></div>"; die;
		  }
		  else if($request['STATUS'] == 'TXN_SUCCESS'){
			$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING,true)->save();
			echo "<div style='width:100%; text-align:center;'><b>Thank you, your payment was successful.<b></div>"; die;  
		  }
	  }
	  catch (\Exception $ex) {
        echo "<div style='width:100%; text-align:center;'><b>Something went wrong111.<b></div>"; die;
    }

	     // $this->_view->loadLayout();
        // $this->_view->getLayout()->initMessages();
        // $this->_view->renderLayout();
    }
}