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
if(isset($_REQUEST) && !empty($_REQUEST))
{
	print_r($_REQUEST);
	foreach($_REQUEST as $item)
	{
		echo $item;
	}
}
$resource       	= $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     	= $resource->getConnection();
$delivery_challan_data 	= $resource->getTableName('delivery_challan_data'); //gives table name with prefix
$sql 			= "select * from ".$delivery_challan_data." where status!='closed'";
$result = $connection->fetchAll($sql);
?>
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
if($result)
{
	echo "<table><tr><th>Delivery Challan No.</th><th>Challan Date</th><th>From</th><th>All SKUs</th><th>Returnable</th><th>Order ID</th><th>Status</th><th>Action</th></tr>";
        foreach($result as $row)
	{
			$return_received_flag = '';
			$id = $row['id'];
			if($row['returnable'] == 'Yes')
			{
				$return_received_flag = " || <a href = '".$baseUrl."wms/wms_return_received.php?id=".$id."'>Return Received</a>";
			}
			else{
				$return_received_flag = " || <a href = '".$baseUrl."wms/wms_challan_closed.php?id=".$id."'>Mark Challan Closed</a>"; 
			}
			echo "<tr><td>".$row['id']."</td><td>".$row['challan_date']."</td><td>".$row['from']."</td><td>".$row['all_skus']."</td><td>";
			if($row['returnable']){echo $row['returnable'];} else{ echo "No";}
			echo "</td><td>".$row['order_id']."</td><td>".$row['status']."</td><td><a href='".$baseUrl."wms/wms_delivery_challan_print.php?id=".$row['id']."'>Print</a>".$return_received_flag."</td></tr>";
	}
	echo "</table>";
}
?>
