<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$tableName = $resource->getTableName('wms_users'); //gives table name with prefix
if(isset($_REQUEST['username']) && !empty($_REQUEST['username']) && isset($_REQUEST['password']) && !empty($_REQUEST['password']))
{
	$username = $_REQUEST['username'];
	$password = $_REQUEST['password'];
	$sql = "select * FROM " . $tableName." Where username='".$username."' and password='".$password."'";
	$result = $connection->fetchAll($sql);
	if($result)
	{
		$_SESSION['role'] 	= $result[0]['role'];
		$_SESSION['username']  	= $username;
		$_SESSION['id']		= $result[0]['id'];
		if($result[0]['role']=='VIEWER')
		{
			header("Location: ".$baseUrl."wms/wms_inventory_report.php");
			exit();
		}
		else
		{
			header("Location: ".$baseUrl."wms_inventory_inwards.php");
                        exit();
		}

	}
	else{ echo "Either Username or Password is wrong. "; echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to try again.";}

}
?>
