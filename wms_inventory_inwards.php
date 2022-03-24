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
	echo "<div class='logout'><a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a> <a href='".$baseUrl."wms/wms_damage_reject_report.php'> Damage / Reject Report ||</a>";
	echo "<a href='".$baseUrl."wms_supplier_product.php'>Supplier's products Report || </a>";
	if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ALL-WH'){echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>"; }
	echo "<a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a><a href='".$baseUrl."wms/wms_PO_close_listing.php'>Closed Purchase Orders || </a><a href='".$baseUrl."wms/product_planning_report.php'>Product Planning Report || </a><a href='".$baseUrl."sku_inventory_history.php'>SKU Inventory History || </a>";
		echo "<a href='".$baseUrl."wms/wms_delivery_challan_category_selection.php'>Delivery Challan Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";
		echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
				echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
	echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
	die("You're not authorised to see this page.");
}

$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

$collection = $productCollection->create()
	->addAttributeToSelect('*')
	//->addAttributeToSelect('name')
	    ->load();
?>
<form name="searchsku" action="wms/wms_inwards.php" method="post">
<h3 style="padding:10px;">Search Product either by SKU or Name</h3>
<label for="Search by SKU" style="padding:10px;">Enter SKU in the box or select from list : </label>
<input type="text" name="sku_textbox" id="skutext">
<select id="sku" name="sku_dropdown" onchange="skuDropDown()">
<option value="0">Select Sku</option>
<?php
$productNameOptions = "<option value='0'>Select Product Name</option>";
foreach($collection as $item)
{
	$productNameOptions .= "<option value='".$item->getData()['sku']."'>".$item->getData()['name']."</optoin>"; 
	echo "<option value='".$item->getData()['sku']."'>".$item->getData()['sku']."</optoin>";
}
?>
</select>
<br>
</br>
<label for="Search by Name" style="padding:10px;" >Search by Name : </label>
<select id="name" name="name_dropdown" onchange="nameDropDown()"><?php echo $productNameOptions ?></select>
<br><br>
<input type="submit" value="Search" style="margin:10px;" onClick="return checkDropDownValues();">
</form>
<script>
function skuDropDown()
{
	var sku = document.getElementById("sku").value;
	document.getElementById("name").value = sku;
}
function nameDropDown()
{
        var name = document.getElementById("name").value;
        document.getElementById("sku").value = name;
}
function checkDropDownValues()
{
	var sku = document.getElementById("sku").value;
	var name = document.getElementById("name").value;
	var skutext = document.getElementById("skutext").value;
	if(sku == 0 && name == 0 && skutext == '')
	{
		alert("You have to select atleast one, Either name or sku to continue.");
		return false;
	}
	else{return true;}
}
</script>
