<?php
namespace Cod\Eligible\Observer;

class Disablecod implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
     //$order= $observer->getData('order');
	 //$order->doSomething();
	if($observer->getEvent()->getMethodInstance()->getCode()=="checkmo"){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
                $items = $cart->getQuote()->getAllItems();
                foreach($items as $item) {
                        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                        if(!$product->getEligibleForCashOnDelivery())
                        {
                                $checkResult = $observer->getEvent()->getResult();
                                $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
                                //exit;
                        }
                }
                //$checkResult = $observer->getEvent()->getResult();
                //$checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
        }
  }
}
