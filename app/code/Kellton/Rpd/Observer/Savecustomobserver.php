<?php
namespace Kellton\Rpd\Observer;

class Savecustomobserver implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
     
  	$order = $observer->getEvent()->getOrder();
	$readinessdate ='10-12-2019';
	$pickupdate ='12-12-2019';
	$deliverydate ='15-12-2019';
	$order->setReadinessdate($readinessdate);
	$order->setPickupdate($pickupdate);
	$order->setDeliverydate($deliverydate);

     return $this;
  }
}