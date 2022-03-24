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
foreach ($collection as $product){ echo "++".$product->getSku()."++"; //$skus = array("BR1BESRCB0007","SD1RWSGPG0010","SD1RWSGPG0021","CL1BPRGVS0084","CL1BPRGVS0085","CL1BPRGVS0086","CL1BPRGJR0093","CL1BPRGJR0094","CL1BPRGTY0098","CN1BGAPQT0012","CN1BGAPQT0014","CN1BGAPBG0017"); 
//if(in_array($product->getSku(), $skus)){continue;}
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); /* $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku'); $qty = $StockState->execute($product->getSku());
	$salable_qty = $qty[0]['qty']; */
	//$objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
	//$stockItem = $objectManager->get('\Magento\CatalogInventory\Model\Stock\StockItemRepository');
	//$productStock = $stockItem->get($product->getId());
	//$main_qty = $productStock->getQty();
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 /*$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
 $main_qty = $StockState->getStockItemBySku($product->getSku())->getQty();
	$main_qty = 0;
	if($salable_qty){$main_qty = 1;}*/
	$product_desc = $product->getDescription();
	$categoryIds = $product->getCategoryIds();
 $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
if($categoryIds)
{
$categories = $categoryCollection->create()
                                 ->addAttributeToSelect('*')
                                 ->addAttributeToFilter('entity_id', $categoryIds);
if($categories)
{
$product_categories_name = ''; 
foreach ($categories as $category) {
    	$product_categories_name .= $category->getName() . ' > ';
}
 	$product_categories_name = rtrim($product_categories_name," > ");
}}	$product_short_desc  = $product->getShortDescription();
	$product_sku = $product->getSku();
	//echo "<pre>"; print_r($product->getData()); die;
	$store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
	$imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
	$productPrice=0;
	$productPrice = $product->getFinalPrice();
	$productFactory = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->getById($product->getId());
	if($productFactory->getTierPrice()[0]['website_price'])
	{
		if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
    			$productRateId = $taxAttribute->getValue();
    			$rate = $objectManager->create('\Magento\Tax\Api\TaxCalculationInterface')->getCalculatedRate($productRateId);
    			//$rate = $taxCalculation->getRate();
    			$priceIncludingTax = $productFactory->getTierPrice()[0]['website_price'] + ($productFactory->getTierPrice()[0]['website_price'] * ($rate / 100));
    			$productPrice = round($priceIncludingTax);
		}
	}
	else{
		 if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
                        $productRateId = $taxAttribute->getValue();
                        $rate = $objectManager->create('\Magento\Tax\Api\TaxCalculationInterface')->getCalculatedRate($productRateId);
                        //$rate = $taxCalculation->getRate();
                        $priceIncludingTax = $productFactory->getPrice() + ($productFactory->getPrice() * ($rate / 100));
                        $productPrice = round($priceIncludingTax);
                }
	}
	/* if($product->getSku()=='ME1HPAUPE0003'){ $productFactory = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->getById($product->getId()); echo "<pre>"; print_r($productFactory->getPrice()); echo $productFactory->getFinalPrice(); echo $productFactory->getSpecialPrice(); 
	//print_r($product->getCustomAttribute('tax_class_id')); die;
if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
    $productRateId = $taxAttribute->getValue();
    $rate = $objectManager->create('\Magento\Tax\Api\TaxCalculationInterface')->getCalculatedRate($productRateId);
    //$rate = $taxCalculation->getRate();
    echo $priceIncludingTax = $productFactory->getPrice() + ($productFactory->getPrice() * ($rate / 100));
    echo $productPrice = round($priceIncludingTax); die;
}

print_r($productFactory->getTierPrice()[0]['website_price']); die; echo $product->getSpecialPrice(); die;} */
	if($product->getSpecialPrice())
	{
		$price = $product->getSpecialPrice();
	}
	else
	{
		$price = $product->getPrice();
	}
	// Attempt insert query execution
	$sql = "INSERT INTO `product_feed_template_ad` (`sku`, `name`, `description`, `short_description`, `product_categories_name`, `price`, `url_key`, `base_image`, `qty`, `is_in_stock`) VALUES ('".$product_sku."','".$product->getName()."','".mysqli_real_escape_string($link, strip_tags($product_desc))."','".mysqli_real_escape_string($link, strip_tags($product_short_desc))."','".$product_categories_name."','".$productPrice."','".$product->getProductUrl()."','".$imageUrl."','1','1')";
	if(mysqli_query($link, $sql)){
    		echo "Records inserted successfully.". PHP_EOL;
	} else{
    		echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
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

