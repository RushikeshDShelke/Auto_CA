<?php

namespace Kellton\Shipmentemail\Controller\Index;

class Shipcharge extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {

        // $this->_view->loadLayout();
        // $this->_view->getLayout()->initMessages();
        // $this->_view->renderLayout();

      //echo '++++++'; exit;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
	    $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode(); 
	    $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode); 
	    echo $currencySymbol = $currency->getCurrencySymbol();

		$cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
		 
		// $subTotal = $cart->getQuote()->getSubtotal();
		$grandTotal = $cart->getQuote()->getGrandTotal();
      
       $quote = $objectManager->get('\Magento\Checkout\Model\Session'); 

       $quote->getQuote()->getShippingAddress()->getCity();

       $amount = $quote->getQuote()->getShippingAddress()->getShippingAmount();
	   $totals = $quote->getQuote()->getTotals();

	   if(isset($totals['tax']) && $totals['tax']->getValue()) {
			$tax = round($totals['tax']->getValue());
		} else {
			$tax = '0';
		}
	   if(isset($totals['discount']) && $totals['discount']->getValue()) {
			$discount = round($totals['discount']->getValue());
			echo '<p class="subtotal"><span class="label">Discount: </span> <span class="price">'.$currencySymbol.$discount.'</span></p>';
		} else {
		}	
       echo '<p class="subtotal"><span class="label">Estimated Shipping: </span> <span class="price">'.$currencySymbol.$amount.'</span></p>';
	   echo '<p class="subtotal"><span class="label">Tax (included above): </span> <span class="price">'.$currencySymbol.$tax.'</span></p>';
       echo '<p class="subtotal"><span class="label">Total: </span> <span class="price">'.$currencySymbol.$grandTotal.'</span></p>';
       

    }
}