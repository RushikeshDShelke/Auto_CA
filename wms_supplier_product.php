<?php
session_start();
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
if(isset($_SESSION['id']) && !empty($_SESSION['id']) && isset($_SESSION['username']) && !empty($_SESSION['username']) && isset($_SESSION['role']) && !empty($_SESSION['role']))
{
	echo "<div class='top-container' style='background: #C3D9FF;
    				padding: 5px 10px 10px 5px;
    				margin-top: -8px;
    				margin-left: -8px;
    				margin-right: -8px;'>
	<div class='welcome' style='float:left;'><span>Welcome, ".$_SESSION['username']." || &nbsp;</span></div>";
	echo "<div class='logout'><a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a><a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a>";
				echo "<a href='".$baseUrl."wms/wms_delivery_challan_generation.php'>Delivery Challan Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";
				echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
				echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
	echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
	die("You're not authorised to see this page.");
}

?>
<form name="searchsku" action="wms/wms_supplier_products_report.php" method="post">
<!--<h3 style="padding:10px;">Search Product either by SKU or Name</h3> -->
<label for="Search by SKU" style="padding:10px;">Select Supplier name : </label>
<select id="sku" name="supplier_list">
<?php
$customergroupId = 5; //Supplier Group Id
$customerObj = $objectManager->create('Magento\Customer\Model\Customer')->getCollection()->addFieldToFilter('group_id', $customergroupId);
echo "<option value='0'>Select Supplier</option>";
foreach($customerObj as $customerObjdata )
{
        echo "<option value='".$customerObjdata->getData()['entity_id']."'>".$customerObjdata->getData()['firstname']." ".$customerObjdata->getData()['lastname']."</option>";
        //echo "<pre>";print_r($customerObjdata->getData()); die;
}
?>
</select>
<br>
</br>
<input type="submit" value="Search" style="margin:10px;" onClick="return checkDropDownValues();">
</form>
<script>
function checkDropDownValues()
{
	var sku = document.getElementById("supplier_list").value;
	if(sku == 0)
	{
		alert("You have to select a supplier.");
		return false;
	}
	else{return true;}
}
</script>
