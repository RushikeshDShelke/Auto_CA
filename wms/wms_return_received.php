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
$resource               = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection             = $resource->getConnection();
$delivery_challan_data  = $resource->getTableName('delivery_challan_items'); //gives table name with prefix
$sql                    = "select * from ".$delivery_challan_data." where challan_id=".$_REQUEST['id'];
$result = $connection->fetchAll($sql);
if($result)
{
	echo "<form align='center' name='return_received' method='post' action='wms_delivery_challan_list.php'>";
	foreach($result as $row)
	{
		echo '<input type="checkbox" id="'.$row['product_id'].'" name="'.$row['sku'].'" value="'.$row['sku'].'"><label for="'.$row['product_id'].'"> '.$row['name'].'</label><br>';
	}
	echo "<input type='submit' value='Return Received'></form>";
	//echo '<input type="checkbox" id="" name="vehicle1" value="Bike"><label for="vehicle1"> I have a bike</label><br>';
}
?>
