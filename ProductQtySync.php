<?php
//print_r($_REQUEST);
//die('Here');
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/earth-fables/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
print_r($argv); 
if (isset($argc) && $argc > 1) {
	$myfile 		= fopen("/var/www/html/testfile.txt", "w"); fwrite($myfile, $argv[1]);
	$productRepository 	= $objectManager->get('\Magento\Catalog\Model\ProductRepository');
	$productObj 		= $productRepository->get($argv[1]);
	$stockRegistry 		= $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
	$stockItem 		= $stockRegistry->getStockItemBySku($argv[1]);
	//echo $stockItem->getQty(); die;
    	$stockItem->setQty($argv[2]);
    	$stockItem->setIsInStock((bool)$argv[2]); // this line
    	$stockRegistry->updateStockItemBySku($argv[1], $stockItem);
} 

?>
