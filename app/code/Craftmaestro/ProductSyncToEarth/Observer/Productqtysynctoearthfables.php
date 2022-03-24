<?php
namespace Craftmaestro\ProductSyncToEarth\Observer;

class Productqtysynctoearthfables implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
	$product = $observer->getEvent()->getProduct();
    	//echo "proname: ".$product->getSku();
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 	$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
 	$MainQty = $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
	//exec ('/usr/bin/php -f /var/www/html/ProductQtySync.php '.$product->getSku().' '.$MainQty);
     	//exec ("curl --data 'sku=".$product->getSku()."&qty=".$MainQty."' https://earth-fables.craftmaestros.com/ProductQtySyncCraft.php");
	return $this;
  }
}
