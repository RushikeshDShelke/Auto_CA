<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
        /* echo "<div class='top-container' style='background: #C3D9FF;
                                padding: 5px 10px 10px 5px;
                                margin-top: -8px;
                                margin-left: -8px;
                                margin-right: -8px;'>
        <div class='welcome' style='float:left;'><span>Welcome, ".$_SESSION['username']." ||</span></div>";
                                echo "<div class='logout'>";
                                if($_SESSION['role'] != 'VIEWER')
                                {
                                        echo "<a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a>";
                                }
                                echo "<a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a>";
                                if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ALL-WH'){echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>"; }
                                //echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a>";
				echo "<a href='".$baseUrl."wms/wms_delivery_challan_generation.php'>Delivery Challan Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";
                                echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
                                echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>"; */

}
else{
        echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
$from_date = '';
$to_date = '';
if(isset($_REQUEST['from']) && $_REQUEST['from'])
{
	$from_date = date('Y-m-d',strtotime($_REQUEST['from']));
	$from_date = $from_date." 00:00:00";
}
if(isset($_REQUEST['to']) && $_REQUEST['to'])
{
	$to_date = date('Y-m-d',strtotime($_REQUEST['to']));
	$to_date = $to_date." 23:59:59";
}

$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
$sql = "SELECT `created_at`, `warehouse`, `sku`, `challan_number`, `invoice_number`, `challan_date`, `invoice_date`, `created_by`, `qty_addition`, `qc_reject`, `short_receipt`, `damage_warehouse`
FROM `wms_inwards_data` where `qc_reject`";
$sql = "SELECT `created_at`, `warehouse`, `sku`, `challan_number`, `invoice_number`, `challan_date`, `invoice_date`, `created_by`, `qty_addition`, `qc_reject`, `short_receipt`, `damage_warehouse`, `reduce_qty`, `goods_return`
FROM `wms_inwards_data` where (reduce_qty!='' AND reduce_qty!=0 AND reduce_qty is not null) or (qc_reject!='' AND qc_reject!=0 AND qc_reject is not null) or (short_receipt !='' and short_receipt !=0 AND short_receipt is not null) or (damage_warehouse != '' and damage_warehouse !=0 AND damage_warehouse is not null) AND created_at <='".$from_date."' AND created_at <='".$to_date."'";

//and created_at >= "'.$from_date.'" and created_at <="'.$to_date.'"";
$result = $connection->fetchAll($sql);
$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
$date2_ts       = strtotime(date("Y-m-d"));
$formatted_array = array();
foreach($result as $row)
{
	$date1_ts 	= strtotime($row['created_at']);
	$diff           = $date2_ts - $date1_ts;
        $no_of_days     = round($diff / 86400);
	$productObj 	= $productRepository->get($row['sku']);
	$row['mc_name'] = $productObj->getResource()->getAttribute('creator_id')->getFrontend()->getValue($productObj);
	$row['product_name'] = $productObj->getName();
	$formatted_array[] = $row;

}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Order_damage_reject_report.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('Entry Date', 'Warehouse', 'SKU', 'challan_number', 'invoice_number', 'Challan Date', 'Invoice Date', 'Entry By', 'Invoice_total_qty', 'QC Reject', 'Short Receipt','Damage in WH', 'Reduce Qty', 'Reason', 'MC Name', 'Product Name'));
if (count($formatted_array) > 0) {
    foreach ($formatted_array as $row) {
        fputcsv($output, $row);
    }
}
?>
