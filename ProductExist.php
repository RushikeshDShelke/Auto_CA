<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
$sku = $_REQUEST['sku'];
$product = $objectManager->get('Magento\Catalog\Model\Product');
$response = array();
if($product_id = $product->getIdBySku($sku)) {
	$product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
	$StockState     	= $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $qty            	= $StockState->execute($product->getSku());
	$total_salable_qty 	= $qty[0]['qty'];
	$getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
	$AllInventoryBySources  = $getSourceItemsDataBySku->execute($_REQUEST['sku']);
	$total_main_qty_CM = 0;
        $CM_GGN_Inventory = 0;
        foreach($AllInventoryBySources as $sourceInventory)
        {
                $total_main_qty_CM = (int)$total_main_qty_CM + (int)$sourceInventory['quantity'];
        }
    	$response = array("exist"=>1,"salable_qty"=>$total_salable_qty,"main_qty"=>$total_main_qty_CM);
}
else{
	$response = array("exist"=>0);
}
echo json_encode($response);
?>
