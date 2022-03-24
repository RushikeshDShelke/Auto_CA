<?php
session_start();
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
        echo "<div class='top-container' style='background: #C3D9FF;
                                padding: 5px 10px 10px 5px;
                                margin-top: -8px;
                                margin-left: -8px;
                                margin-right: -8px;'>
        <div class='welcome' style='float:left;'><span>Welcome, ".$_SESSION['username']." || &nbsp;</span></div>";
        echo "<div class='logout'><a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms/wms_damage_reject_report.php'> Damage / Reject Report ||</a>";
	if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ALL-WH'){echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>"; }
	echo "<a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a>";
	echo "<a href='".$baseUrl."wms/wms_delivery_challan_generation.php'>Delivery Challan Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";	
	echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
                                echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
        echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$po_order_data  = $resource->getTableName('po_order_data'); //gives table name with prefix

?>
<style>
table, th, td {
    padding: 10px;
    border: 1px solid black;
    border-collapse: collapse;
}
</style>
<table>
<tr>
<th>PO_no</th>
<th>Status</th>
<th>Supplier_id</th>
<th>Payment Term</th>
<th>Freight Term</th>
<th>created_at</th>
<th>created_by</th>
<th>Action</th>
</tr>
<?php
$sql = "Select * FROM " .$po_order_data. " where status='closed'";
$result = $connection->fetchAll($sql);
if($result)
{
	foreach($result as $row)
	{
		echo "<tr><td>".$row['po_no']."".$row['id']."</td><td>".$row['status']."</td><td>".$row['supplier_id']."</td><td>".$row['payment_term']."</td><td>".$row['freight_term']."</td><td>".$row['created_at']."</td><td>".$row['created_by']."</td><td>";
		if($row['status'] == 'requested_for_approval' || $row['status'] == 'rejected')
		{
			$params = "?supplier_id=".$row['supplier_id']."&payment_term=".$row['payment_term']."&freight_term=".$row['freight_term']."&po_no=".$row['po_no']."".$row['id'];
			$item_sql = "Select * FROM po_order_item where po_no='".$row['id']."'";
			$resultItems = $connection->fetchAll($item_sql);
			foreach($resultItems as $item)
			{
				$params = $params."&".$item['sku']."=".$item['qty_ordered']."&".$item['sku']."_date=".$item['ready_date'];
			}
			echo "<a href='".$baseUrl."wms/wms_preview.php".$params."'>View</a>";
			echo " || <a href='".$baseUrl."wms/wms_PO_edit.php".$params."'>Edit</a>";	
		}
		if($row['status'] == 'approved')
		{
			$params = "?supplier_id=".$row['supplier_id']."&payment_term=".$row['payment_term']."&freight_term=".$row['freight_term']."&po_no=".$row['po_no']."".$row['id'];
                        $item_sql = "Select * FROM po_order_item where po_no='".$row['id']."'";
                        $resultItems = $connection->fetchAll($item_sql);
                        foreach($resultItems as $item)
                        {
                                $params = $params."&".$item['sku']."=".$item['qty_ordered']."&".$item['sku']."_date=".$item['ready_date'];
                        }
			echo "<a href='".$baseUrl."wms/po_download.php".$params."' target='_blank'>Download</a>";
			echo " || <a href='".$baseUrl."wms/po_close.php".$params."'>Mark PO Complete</a>";	
		}
		echo "</td></tr>";
	}
}
?>
</table>
