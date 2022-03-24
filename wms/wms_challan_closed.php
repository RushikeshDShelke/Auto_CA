<style>
      table,
      th,
      td {
        padding: 10px;
        border: 1px solid black;
        border-collapse: collapse;
      }
    </style>
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
if(isset($_REQUEST['id']) && $_REQUEST['id'])
{
	$resource               = $objectManager->get('Magento\Framework\App\ResourceConnection');
	$connection             = $resource->getConnection();
	$delivery_challan_data  = $resource->getTableName('delivery_challan_data'); //gives table name with prefix
	echo $sql                    = "select * from ".$delivery_challan_data." where id=".$_REQUEST['id'];
	$result 		= $connection->fetchAll($sql);
	print_r($result); die;
	if($result)
	{
		$sqlUpdate = "update ".$delivery_challan_data." set status='closed' where id=".$_REQUEST['id'];
		
	}
}	
