<?php
namespace Kellton\Ewaybillno\Observer;
use Magento\Framework\App\RequestInterface;

class Saveewaybillobserver implements \Magento\Framework\Event\ObserverInterface
{
     
     protected $_order;
	 protected $_ewaybillno;
	 protected $request;
     
    public function __construct(
		\Magento\Catalog\Block\Product\Context $context,            
		\Magento\Sales\Api\Data\OrderInterface $order,
		\Kellton\Ewaybillno\Model\EwaybillnoFactory   $ewaybillno,
		RequestInterface $request     
		) {
			
		$this->_order            =  $order;
		$this->_ewaybillno       =  $ewaybillno;   
		$this->request 			= $request;       
    }


	public function execute(\Magento\Framework\Event\Observer $observer)
  	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/deliverytrack.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		if($post_data = $this->request->getParams()){
		$post_data = $this->request->getParams();
		$shipment  = $observer->getEvent()->getShipment();
		$orders    = $shipment->getOrder();
		$orderId   = $orders->getId();
		$order     = $this->_order->load($orderId);

		foreach($order->getShipmentsCollection() as $shipment)
		{
			if(count($shipment->getData())){
				if($post_data['tracking'][1]['number']){
					$eObj = $this->_ewaybillno->create();
					$eObj->setAwb($post_data['tracking'][1]['number']);
					$eObj->setShipmentId($shipment->getIncrementId());
					try{
						$eObj->save();
					}
					catch(Exception $ex){
						$logger->info('Error Saving: Kellton\Ewaybillno\Observer\Saveewaybillobserver');
					}
				}
			}
		}

			return $this;
	}
			
  	}

  
}
