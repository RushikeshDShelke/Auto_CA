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
$link = mysqli_connect("localhost", "root", "crA7t5@dbma!", "Craftlive_new");
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
foreach ($collection as $product){ echo "++".$product->getSku()."++"; //$skus = array("BR1BESRCB0007","SD1RWSGPG0010","SD1RWSGPG0021","CL1BPRGVS0084","CL1BPRGVS0085","CL1BPRGVS0086","CL1BPRGJR0093","CL1BPRGJR0094","CL1BPRGTY0098","CN1BGAPQT0012","CN1BGAPQT0014","CN1BGAPBG0017"); 
$skus = array("CN1BGAPTC0045"); 
echo "--".$product->getTypeId()."--";
if(in_array($product->getSku(), $skus)){continue;}
if($product->getTypeId()!='simple'){continue;}
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku'); $qty = $StockState->execute($product->getSku());
	$salable_qty = $qty[0]['qty'];
	//$objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
	//$stockItem = $objectManager->get('\Magento\CatalogInventory\Model\Stock\StockItemRepository');
	//$productStock = $stockItem->get($product->getId());
	//$main_qty = $productStock->getQty();
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 	$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
 	$main_qty = $StockState->getStockItemBySku($product->getSku())->getQty();
	$product_desc = $product->getDescription();
	$product_sku = $product->getSku();
	// Attempt insert query executioni
	if($main_qty != $salable_qty)
	{
	$sql = "INSERT INTO salable_main_diff_stock (product_name, sku, product_desc, salable_qty, main_qty) VALUES ('".$product->getName()."','".$product_sku."','','".$salable_qty."','".$main_qty."')"; 
	if(mysqli_query($link, $sql)){
    		echo "Records inserted successfully.". PHP_EOL;
	} else{
    		echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	}
	}
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

