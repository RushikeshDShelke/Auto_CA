<?php
session_start();
error_reporting(0);

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$delivery_challan_addresses_data = $resource->getTableName('delivery_challan_addresses_data'); //gives table name with prefix
$address_id = 0;
$challan_date = '';
$from = '';
$order_id = '';
$challan_id = 0;
//print_r($_REQUEST);
//die;
//$_REQUEST = array_filter($_REQUEST);
//echo "<pre>";print_r($_REQUEST);
//die;
if(isset($_REQUEST['order-id']) && $_REQUEST['order-id'])
{
	try {
  		$order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($_REQUEST['order-id']);
        } catch (Exception $e) {}
	die("There is no such order id exist, Please go back and enter correct order id");

}
if(isset($_REQUEST['address_data']) && $_REQUEST['address_data'])
{
	$insertData = ["address_data"=>$_REQUEST['address_data']];
	$result = $connection->insert($delivery_challan_addresses_data, $insertData);
        if($result)$address_id = $connection->lastInsertId();
}
if(isset($_REQUEST['address_list']) && $_REQUEST['address_list'] && !$address_id)
{
	$address_id = $_REQUEST['address_list'];
}
if(isset($_REQUEST['challan_date']) && $_REQUEST['challan_date'])
{
        $challan_date = $_REQUEST['challan_date'];
}
if(isset($_REQUEST['from']) && $_REQUEST['from'])
{
        $from = $_REQUEST['from'];
}
$skusList = array();
foreach($_REQUEST as $key=>$value)
{
	 if($key != 'challan_date' && $key != 'from' && $key != 'others' && $key != 'address_list' && $key != 'address_data' && $key != 'order-id' && strpos($key,'_qty')===false)
	$skusList[] = $key; 
}
//print_r($skusList); 
$all_skus = implode(",",$skusList);
//die;
if(isset($_REQUEST['all_skus']) && $_REQUEST['all_skus'])
{
        $all_skus = $_REQUEST['all_skus'];
	$skusList = explode(",",$all_skus);
}
$returnable = Yes;
if(isset($_REQUEST['order-id']) && $_REQUEST['order-id'])
{
        $order_id = $_REQUEST['order-id'];
	$returnable = No;
}
$insertData = ["challan_date"=>$challan_date,
		"from"=>$from,
		"all_skus"=>$all_skus,
		"returnable"=>$returnable,
		"order_id"=>$order_id,
		"address_id"=>$address_id,
		"status"=>"Active"
		];
$resultChallan = $connection->insert("delivery_challan_data", $insertData);
if($resultChallan)$challan_id = $connection->lastInsertId();
if($skusList)
{
	foreach($skusList as $sku)
        {
                $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                $productObj = $productRepository->get($sku);
                $productName = $productObj->getName();
                $price = $productObj->getPrice();
                $productId = $productObj->getId();

                $skuInsertData = array("product_id"=>$productObj->getId(),
					"sku"=>$productObj->getSku(),
					"quantity"=>$_REQUEST[$productObj->getSku()."_qty"],
                                        "name"=>$productObj->getName(),
                                        "price"=>$productObj->getPrice(),
                                        "challan_id"=>$challan_id,
					"status"=>"Active"
                                        );
		$connection->insert("delivery_challan_items", $skuInsertData);
        }
}

echo "Your Delivery challan ".$challan_id." has been saved and printing the delivery challan...";
sleep(5);
header("Location: ".$baseUrl."wms/wms_delivery_challan_print.php?id=".$challan_id); /* Redirect browser */
exit();

?>
