<?php
session_start();
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 */
use Magento\Framework\App\Bootstrap;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
require __DIR__ . '/../app/bootstrap.php';
$params         = $_SERVER;
$bootstrap      = Bootstrap::create(BP, $params);
$objectManager  = $bootstrap->getObjectManager();
$state          = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager   = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl        = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
if(isset($_SESSION['id']) && !empty($_SESSION['id']) && isset($_SESSION['username']) && !empty($_SESSION['username']) && isset($_SESSION['role']) && !empty($_SESSION['role']))
{
        echo "<div class='top-container' style='background: #C3D9FF;
                                padding: 5px 10px 10px 5px;
                                margin-top: -8px;
                                margin-left: -8px;
                                margin-right: -8px;'>
        <div class='welcome' style='float:left;'><span>Welcome, ".$_SESSION['username']." ||</span></div>";
        echo "<div class='logout'><a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
                                echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
        echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
//$tableName      = $resource->getTableName('wms_users'); //gives table name with prefix
$wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
//Select Data from table
if(!isset($_REQUEST['id']) || empty($_REQUEST['id']))
{
	die("No Record Found");
}
$challan_no = $_REQUEST['challan_no'];
$challan_date = $_REQUEST['challan_date'];
$invoice_no = $_REQUEST['invoice_no'];
$invoice_date = $_REQUEST['invoice_date'];
$updateSql = "update ".$wms_inward_table." set challan_number='".$challan_no."',challan_date='".$challan_date."',invoice_number='".$invoice_no."',invoice_date='".$invoice_date."' where id=".$_REQUEST['id'];
$connection->query($updateSql);
header("Location: ".$baseUrl."wms/wms_inventory_report.php");
exit();
