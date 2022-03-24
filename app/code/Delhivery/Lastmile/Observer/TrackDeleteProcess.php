<?php
namespace Delhivery\Lastmile\Observer;

use Magento\Framework\Event\ObserverInterface;

class TrackDeleteProcess implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $track = $observer->getEvent()->getTrack();
		$order = $track->getShipment()->getOrder();
		$shippingMethod = $order->getShippingMethod();
        // your code for sms here
		//echo $track->getCarrierCode();
		//echo $track->getNumber();
		if (!$shippingMethod) {
				return;
		}
		// Process only Delhivery Lastmile methods
		if($track->getCarrierCode() != 'delhivery')
		{
			return;
		}
		$objectManager2 = \Magento\Framework\App\ObjectManager::getInstance();
		$userModel = $objectManager2->create('Delhivery\Lastmile\Model\Awb');
		$userModel=$userModel->getCollection()->addFieldToFilter("awb",$track->getNumber())->getFirstItem();
		
		$objectManager4 = \Magento\Framework\App\ObjectManager::getInstance();
		$updateAwb = $objectManager4->create('Delhivery\Lastmile\Model\Awb')->load($userModel->getId());
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
		$updateAwb->setShipmentHeight('')->save();
		
    }
}