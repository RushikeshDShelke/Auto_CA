<?php 
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
<title>Purchase Order Form</title>
<link rel="stylesheet" type="text/css" href="view.css" media="all">
<script type="text/javascript" src="view.js"></script>
<script type="text/javascript" src="calendar.js"></script>
</head>
<style>
table, th, td {
    padding: 10px;
    border: 1px solid black;
    border-collapse: collapse;
}
</style>
<body id="main_body" >
<?php 
if(isset($_SESSION['id']) && !empty($_SESSION['id']) && isset($_SESSION['username']) && !empty($_SESSION['username']) && isset($_SESSION['role']) && !empty($_SESSION['role']))
{
        echo "<div class='top-container' style='background: #C3D9FF;
                                padding: 5px 10px 10px 5px;
                                margin-top: -8px;
                                margin-left: -8px;
                                margin-right: -8px;'>
	<div class='welcome' style='float:left;'><span>Welcome, ".$_SESSION['username']." || &nbsp;</span></div>";
	echo "<div class='logout'><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a><a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
        echo "<!--<div class='logout'i style='float:left;'> <a href='".$baseUrl."wms_logout.php'> Logout</a></div>--></div>";
}
else{
	echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
if(isset($_REQUEST['supplier_list']) && !empty($_REQUEST['supplier_list']))
{
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        $collection = $productCollection->create()
        ->addAttributeToSelect('*')
        //->addAttributeToSelect('name')
	->addAttributeToFilter('creator_id', trim($_REQUEST['supplier_list']))
	->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->load();
	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load(trim($_REQUEST['supplier_list']));
//echo $customer->getFirstname(); //Print Customer First Name
//echo $customer->getLastname(); //Print Customer Last Name
        //echo "<pre>"; print_r($collection->getData());
        //print_r($_POST); ?>
        <div class="po_container" style="text-align: center;">
        <form id="PO_form_data" name="PO_form_data"  action="wms_PO_orderData.php">
	<h2>Purchase Order Form of <?php echo $customer->getFirstname()." ".$customer->getLastname() ?></h2>
	<input type="hidden" id="supplier_id" name="supplier_id" value="<?php echo $_REQUEST['supplier_list'] ?>">
	<select name="payment_term" id="payment_term" onchange="paymentTerm(this)" required>
		<option value="">Select payment term</option>
		<option value="consignment" <?php if(array_key_exists('payment_term',$_REQUEST)){ if($_REQUEST['payment_term'] == 'consignment'){ echo "selected";}} ?> >Consignment</option>
		<option value="advanced_payment" <?php if(array_key_exists('payment_term',$_REQUEST)){ if($_REQUEST['payment_term'] == 'advanced_payment'){ echo "selected";}} ?> >Advanced Payment</option>
		<option value="other">Other</option>
	</select>
	<input type="text" name="payment_term_other" id="payment_term_other" style="display:none;" placeholder="Enter other payment term">
	<!--<input type="text" id="Freight_term" name="freight_term" placeholder="Enter Freight Term" required>-->
	<select name="freight_term" id="Freight_term" required>
                <option value="">Select Freight term</option>
                <option value="cm" <?php if(array_key_exists('freight_term',$_REQUEST)){ if($_REQUEST['freight_term'] == 'cm'){ echo "selected";}} ?>>CM</option>
                <option value="vendor" <?php if(array_key_exists('freight_term',$_REQUEST)){ if($_REQUEST['freight_term'] == 'vendor'){ echo "selected";}} ?>>Vendor</option>
        </select>
	<table align='center'>
<?php
	$dateCounter = 0;
        foreach($collection->getData() as $product)
        {
		$productObj = $objectManager->get('Magento\Catalog\Model\Product')->load($product['entity_id']);
		$productStatus = $productObj->getStatus();
?>
	<tr style="<?php if($productStatus == 2) echo 'background-color: #9e9e9e;'; ?>">
	<td><label for="<?php echo $product['sku'] ?>"><?php echo $product['sku'] ?></label></td>
	<td><?php echo $productObj->getName() ?></td>
	<td><input type="text" id="<?php echo $product['sku'] ?>" class="<?php echo $product['sku'] ?>" name="<?php echo $product['sku'] ?>" placeholder='Enter Qty to be purchased' value="<?php if(array_key_exists($product['sku'],$_REQUEST)) echo $_REQUEST[$product['sku']]; ?>"></td>
	<td><?php $requestedDate = ''; if(array_key_exists($product['sku']."_date",$_REQUEST)){$requestedDate = $_REQUEST[$product['sku']."_date"];} echo "Enter Goods Ready Date : <input type='date' id='".$product['sku']."_date' name='".$product['sku']."_date' onchange='if(".$dateCounter."==0){AutoFillDate(this)}' value='".$requestedDate."'>"; ?></td>
	</tr>
<?php
		$dateCounter++;
	}

        echo "</table><input type='hidden' name='supplier_id' value='".trim($_REQUEST['supplier_list'])."'>";
        echo '</br><input type="button" onclick="previewFormat()" value="Preview PO Format" /><input type="submit" value="Submit For Approval">';
        echo "</form></div>";
}
?>
</body>
</html>
<script>
function AutoFillDate(element)
{
	//alert(element.value);
	var formElements = document.getElementById("PO_form_data").elements;
	for (i = 0; i < formElements.length; i++) {
		if (formElements[i].nodeName === "INPUT" && formElements[i].type === "date") {
			formElements[i].value = element.value;
		}
	}
}
function paymentTerm(element)
{
        var end = element.value;
        if(end=='other'){document.getElementById("payment_term_other").style.display = "initial";}
        else{document.getElementById("payment_term_other").style.display = "none"; }
}
function previewFormat()
{
	var payment_term_value = document.getElementById("payment_term").value;
	var formElements = document.getElementById("PO_form_data").elements;
	var PO_data = [];
	var queryParams = '?preview=true';
	var counter = 0;
	// Iterate over the form controls
	for (i = 0; i < formElements.length; i++) {
  		if (formElements[i].nodeName === "INPUT" && formElements[i].type === "text") {
			// Update text input
			if(formElements[i].id && formElements[i].value && formElements[i].id != 'payment_term_other' && formElements[i].id != 'Freight_term' && formElements[i].id != 'maillink' && formElements[i].id != 'id')
			{
				//var sku = formElements[i].id;
				//var qty = formElements[i].value;
				//PO_data[i]['sku'] = formElements[i].id;
				//PO_data[i]['qty'] = formElements[i].value;
				if(counter == 0)
				{
					queryParams = queryParams+'&'+formElements[i].id+'='+formElements[i].value+'&'+formElements[i].id+'_date='+document.getElementById(formElements[i].id+"_date").value;
				}
				else{
					queryParams = queryParams + '&'+formElements[i].id+'='+formElements[i].value+'&'+formElements[i].id+'_date='+document.getElementById(formElements[i].id+"_date").value;
				}
					counter++;
			}
  		}
	}
	if(queryParams)
	{
		if(document.getElementById("supplier_id").value)
		{
			queryParams = queryParams + '&supplier_id='+document.getElementById("supplier_id").value;
		}
		if(payment_term_value)
		{
			if(payment_term_value == 'other')
			{
				queryParams = queryParams + '&payment_term='+document.getElementById("payment_term_other").value;
			}
			else{
				queryParams = queryParams + '&payment_term='+payment_term_value;
			}
		}
		if(document.getElementById("Freight_term").value)
		{
			queryParams = queryParams + '&freight_term='+document.getElementById("Freight_term").value;
		}
	}
	window.location.href = "<?php echo $baseUrl ?>wms/wms_preview.php"+queryParams;
	//console.log(PO_data);
}
</script>
