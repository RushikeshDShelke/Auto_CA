<?php
namespace Delhivery\Lastmile\Observer;

use Magento\Framework\Event\ObserverInterface;

class TrackOrderCancel implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$orderid = $observer->getEvent()->getOrder()->getId();
        //$track = $observer->getEvent()->getTrack();
		//$order = $track->getShipment()->getOrder();
		//$shippingMethod = $order->getShippingMethod();
        // your code for sms here
		//echo $track->getCarrierCode();
		//echo $track->getNumber();
		// Process only Delhivery Lastmile methods
		
		$objectManager2 = \Magento\Framework\App\ObjectManager::getInstance();
		$userModel = $objectManager2->create('Delhivery\Lastmile\Model\Awb');
		$userModel=$userModel->getCollection()->addFieldToFilter("orderid",$orderid);
		
		
		if(count($userModel))
			{	
				foreach($userModel as $Awb)
				{			
					$objectManager4 = \Magento\Framework\App\ObjectManager::getInstance();
					$updateAwb = $objectManager4->create('Delhivery\Lastmile\Model\Awb')->load($Awb->getId());
					//echo "<pre>";
					//print_r($updateAwb->getData());die;
					$updateAwb->setState(4);
					$updateAwb->setStatus('');
					$updateAwb->setOrderid('');
					$updateAwb->setOrderIncrementId('');
					$updateAwb->setShipmentTo('');
					$updateAwb->setShipmentId('');
					$updateAwb->setShipmentLength('');
					$updateAwb->setShipmentWidth('');
					$updateAwb->setStatusType('');
					$updateAwb->setShipmentHeight('')->save();
				}
			}
		
		
		
    }
}