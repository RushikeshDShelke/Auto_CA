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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>WMS Inward Form</title>
<link rel="stylesheet" type="text/css" href="view.css" media="all">
<script type="text/javascript" src="view.js"></script>
<script type="text/javascript" src="calendar.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<?php
if(isset($_SESSION['id']) && !empty($_SESSION['id']) && isset($_SESSION['username']) && !empty($_SESSION['username']) && isset($_SESSION['role']) && !empty($_SESSION['role']))
{
        echo "<div class='top-container' style='background: #C3D9FF;
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
                                echo "<a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms/wms_damage_reject_report.php'> Damage / Reject Report ||</a>";
                                if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ALL-WH'){echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>"; }
                                //echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a>";
				echo "<a href='".$baseUrl."wms/wms_delivery_challan_generation.php'>Delivery Challan Generation || </a>";
				echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";
                                echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div></div>";
                                echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
        echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
?>
<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>WMS Inward Form</title>
<link rel="stylesheet" type="text/css" href="view.css" media="all">
<script type="text/javascript" src="view.js"></script>
<script type="text/javascript" src="calendar.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>-->
<body>

<div id="form_container">

                <h1><a>Delhivery Challan Form</a></h1>
                <form id="form_20409" class="appnitro"  method="post" action="wms_delivery_challan_save.php">
                                        <div class="form_description">
                        <h2>Delhivery Challan Form</h2>
                        <p>Goods gatepass for CM  warehouse.</p>
                </div>
        <ul>
		<li id="li_2">
                	<label class="description" for="challan_date">Challan Date</label>
                	<input type="date" id="challan_date" name="challan_date" value="<?php echo date("Y-m-d"); ?>">
                </li>           
		<li id="li_3">
                	<label class="description" for="from">From</label>
                	<div>
                        	<input id="from" name="from" class="element text medium" type="text" maxlength="255" value="CM-GGN" readonly />
                	</div>
                </li>           
		<li id="li_4" style="max-height: 500px;overflow: auto;width: 100%">
                	<label class="description" for="products">Products</label>
                	<!--<select onchange="return validate();" style="height:300px;" id="products" multiple="multiple">-->
    				<?php 
				$productCollection 	= $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
				$collection 		= $productCollection->create()->addAttributeToSelect('*')->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)->addCategoriesFilter(['in' => $_REQUEST])->load();
				//echo count($collection); die;
				foreach($collection as $item)
				{
					//$productObj = $objectManager->get('Magento\Catalog\Model\Product')->load($item['entity_id']);
                			//$productStatus = $productObj->getStatus();
					//if($productStatus!=1)continue;
					//echo "<option value='".$item->getData()['sku']."'>".$item->getData()['sku']." - ".$item->getData()['name']."</optoin>";						
					echo "<input type='text' name='".$item->getData()['sku']."_qty' placeholder='Enter Requied Qty' onchange='selectCheckbox(this)'><input type='checkbox' id='".$item->getData()['sku']."' name='".$item->getData()['sku']."' value='".$item->getData()['sku']." - ".$item->getData()['name']."' onchange='selecttext(this)'>";
					echo "<label for='".$item->getData()['sku']."'>" .$item->getData()['sku']." - ".$item->getData()['name']."</label></br>";
				}		
				?>
			<!--</select>-->
			<input type='text' name='others_qty' placeholder='Enter Requied Qty'>
			<input type="text" name="others" placeholder="If any other fixtures needs to be added">
			<input type="hidden" name="all_skus" id="all_skus">
                </li>
		<li id="li_5">
			<label class="description" for="products">Non - Returnable</label>
			<input onclick="getReturnableStatus(this)" id="non-returnable" type="checkbox" name="returnable" value="Non - Returnable">
			<input type="text" name="order-id" id="order-id" placeholder='Enter Order Id' style="display:none;">			
		</li>
		<li id="li_6">
			<?php 
			$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection     = $resource->getConnection();
			$delivery_challan_addresses_data = $resource->getTableName('delivery_challan_addresses_data'); //gives table name with prefix
			echo "<select name='address_list' id='address_list' onchange='showNewAddressFields()'>";
			$sql = "select * from ".$delivery_challan_addresses_data;
			$result = $connection->fetchAll($sql);
			if($result)
			{
				echo "<option value=0>Select Address</option>";
				echo "<option value='new'>New Address</option>";
				foreach($result as $row)
				{
					echo "<option value='".$row['address_id']."'>".$row['address_data']."</optoin>";
				}
			}
			echo "</select>";
			?>
		</li>
		<li id="li_7">
			<textarea id="address_data" name="address_data" rows="4" cols="50" placeholder="Enter New Address" style="display:none;"></textarea>
                        <!--<input type="textarea" name="address_data" id="address_data" placeholder='Enter New Address' style="display:none;width: 400px;height: 200px;">-->
		</li>
		<li id="li_8">
			<input type="submit" value="Generate Delivery Challan" onClick="return validate();">
		</li>
	</ul>
</div>
</body>
<script>
function showNewAddressFields()
{
	//$('#address_list').change(function() {
		//alert($("#products :selected").value);
		if($('#address_list').val() == 'new')
		{
			document.getElementById('address_data').style.display = 'block';
		}
		else{
			document.getElementById('address_data').style.display = 'none';
			document.getElementById('address_data').value = '';
		}
	//});
}
function selectCheckbox(ele)
{
	var textValue = ele.value;
	if(textValue)
	{
		var Element_name = ele.getAttribute('name');
		var ret = Element_name.replace('_qty','');
		//alert(ret);
		document.forms['form_20409'][ret].checked = true;

	}
	else{
		var Element_name = ele.getAttribute('name');
                var ret = Element_name.replace('_qty','');
		document.forms['form_20409'][ret].checked = false;
	}
}
function selecttext(ele)
{
	if(ele.checked)
	{
		var Element_name = ele.getAttribute('name');
		document.forms['form_20409'][Element_name+'_qty'].value = 1;
	}
	else{
		var Element_name = ele.getAttribute('name');
                document.forms['form_20409'][Element_name+'_qty'].value = '';
	}
}
function getReturnableStatus(ele)
{
	if(ele.checked)
	{
		document.getElementById('order-id').style.display = 'block';		
	}
	else{
		document.getElementById('order-id').style.display = 'none';
	}
}
function validate()
{
			//$('#products').change(function() {
				/*f($("#products :selected").length > 10)
				{
					alert("10 SKUs can be seleted for a single challan.");
					return false;
				}
				if(document.getElementById('non-returnable').checked && document.getElementById('order-id').value == '')
				{
					alert('In case of Non-returnable, OrderId is required.');
					return false;
}*/
				//var selectedValues = $("#products").val();
	//document.getElementById("all_skus").value = selectedValues;
	var formElements = document.getElementById("form_20409").elements;
        for (i = 0; i < formElements.length; i++) {
		if (formElements[i].nodeName === "INPUT" && formElements[i].value == '') {
                        $(formElements[i]).remove();
                }
        }
				return true;
			//});
}        </script>
<?php
?>
