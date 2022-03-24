<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$productCollection = $obj->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
$stockStatus = $obj->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');
/** Apply filters here */
$collection = $productCollection->addAttributeToSelect('*')
            ->load();
$link = mysqli_connect("localhost", "cmdbuser", "Cr@ft123$", "Craftlive_new");
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
foreach ($collection as $product){ 
//echo "++".$product->getSku()."++"; //$skus = array("BR1BESRCB0007","SD1RWSGPG0010","SD1RWSGPG0021","CL1BPRGVS0084","CL1BPRGVS0085","CL1BPRGVS0086","CL1BPRGJR0093","CL1BPRGJR0094","CL1BPRGTY0098","CN1BGAPQT0012","CN1BGAPQT0014","CN1BGAPBG0017"); 
//if(in_array($product->getSku(), array("CN1BGAPTC0147","CN1BGAPTC0148","CN1BGCLLS0001-1"))){continue;}
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
//$StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku'); $qty = $StockState->execute($product->getSku());
	//$salable_qty = $qty[0]['qty'];
	//$objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
	//$stockItem = $objectManager->get('\Magento\CatalogInventory\Model\Stock\StockItemRepository');
	//$productStock = $stockItem->get($product->getId());
	//$main_qty = $productStock->getQty();
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 //$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
 //$main_qty = $StockState->getStockItemBySku($product->getSku())->getQty();
	//$product_desc = $product->getDescription();
	//$product_sku = $product->getSku();
	// Attempt insert query execution
	//$sql = "INSERT INTO product_feed_template_ad (sku,description,salable_qty,qty,price,mc_price,craftman_name) VALUES ('".$product_sku."','".mysqli_real_escape_string($link, $product_desc)."','".$salable_qty."','".$main_qty."','".$product->getPrice()."','".$product->getMarketplaceFee()."','".$product->getCraftmanName()."')";
	//if(mysqli_query($link, $sql)){
    	//	echo "Records inserted successfully.". PHP_EOL;
	//} else{
    	//	echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	//}
	//$productFactory = $objectManager->get('\Magento\Catalog\Api\Data\ProductInterfaceFactory');
	 $productRepository      = $objectManager->get('\Magento\Catalog\Model\ProductRepository');	
	$productObj = $productRepository->get($product->getSku());
	//if($productObj->setEligibleForCashOnDelivery()=='')
	//{ echo "saved";
	echo $product->getSku(); 
		$productObj->setEligibleForCashOnDelivery(1);
		$productObj->save();
	die;
	//}
	//else{
	//	echo $product->getSku()."++".$product->getEligibleForCashOnDelivery();
	//}
	/* $productFact = 	$productFactory->getById($product->getId());
	$productFact->setData("eligible_for_cash_on_delivery", "Yes");
	$productFactory->save($productFact);
	*/
	//echo $product->getSku()."++".$product->getEligibleForCashOnDelivery(); die;
}
// Close connection
mysqli_close($link);
/*
//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$pendingOrdersCollection = $obj->get("\Magento\Sales\Model\Order")->getCollection()->addFieldToFilter('status', array('eq' => 'pending_payment'));
//$canceledOrdersCollection = $obj->get("\Magento\Sales\Model\Order")->getCollection()->addFieldToFilter('status', array('eq' => 'canceled'));

$to = date("Y-m-d h:i:s"); // current date
$totime = strtotime("-15 minutes", strtotime($to));
$tofinal = date('Y-m-d h:i:s', $totime); // 15 before
$from = strtotime('-1 day', strtotime($to));
$from = date('Y-m-d h:i:s', $from); // 2 days before
$pendingOrdersCollection->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$tofinal));
$orderList = $pendingOrdersCollection->getData();
print_r($orderList);
if($orderList)
{
        foreach($orderList as $singleorder)
	{
		$order = $obj->create('Magento\Sales\Model\Order')->load($singleorder['entity_id']);
		$order->registerCancellation("Order Canceled from Cron after 15 mins")->save();
        } 
}
*/
?>

