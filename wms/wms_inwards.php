<?php 
session_start();

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
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
</head>
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
	echo "<div class='logout'><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms/wms_damage_reject.php'> Damage / Reject Report ||</a>";
	echo "<a href='".$baseUrl."wms_supplier_product.php'>Supplier's products Report || </a>";
	if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ALL-WH'){echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>"; }
	echo "<a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a> ";
	echo "<a href='".$baseUrl."wms/wms_PO_close_listing.php'>Closed Purchase Orders || </a> ";
	echo "<a href='".$baseUrl."wms/wms_delivery_challan_category_selection.php'>Delivery Challan Generation || </a>";
        echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";
	echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
        echo "<!--<div class='logout' style='float:left;'> <a href='".$baseUrl."wms_logout.php'> Logout</a></div>--></div>";
}
else{
	echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
//$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
if(isset($_REQUEST['sku_textbox']) && !empty($_REQUEST['sku_textbox']) || isset($_REQUEST['sku_dropdown']) && !empty($_REQUEST['sku_dropdown']))
{
	if(isset($_REQUEST['sku_textbox']) && !empty($_REQUEST['sku_textbox']))
	$sku                    = $_REQUEST['sku_textbox'];
	else
	$sku 			= $_REQUEST['sku_dropdown'];
	$productRepository 	= $objectManager->get('\Magento\Catalog\Model\ProductRepository');
	$productObj 		= $productRepository->get($sku);
	$supplierId		= $productObj->getCreatorId();
	$customerFactory 	= $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
	$customer 		= $customerFactory->load($supplierId);
	$supplierName 		= $customer->getFirstname()." ".$customer->getLastname();
	$StockState 		= $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
	$main_qty 		= $StockState->getStockQty($productObj->getId(), $productObj->getStore()->getWebsiteId());
	$StockState 		= $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
	$qty 			= $StockState->execute($productObj->getSku());
	$salable_qty 		= $qty[0]['qty'];
	$sourceList 		= $objectManager->get('\Magento\Inventory\Model\ResourceModel\Source\Collection');
	$sourceListArr 		= $sourceList->load();
	/*foreach ($sourceListArr as $sourceItemName) {
    		$sourceCode = $sourceItemName->getSourceCode();
    		$sourceName = $sourceItemName->getName();
	}*/
}
else{
	if(isset($_REQUEST['name_dropdown']) && !empty($_REQUEST['name_dropdown']))
	{
        	$sku                    = $_REQUEST['name_dropdown'];
        	$productRepository      = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        	$productObj             = $productRepository->get($sku);
        	$supplierId             = $productObj->getCreatorId();
        	$customerFactory        = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
        	$customer               = $customerFactory->load($supplierId);
        	$supplierName           = $customer->getFirstname()." ".$customer->getLastname();
        	$StockState             = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        	$main_qty               = $StockState->getStockQty($productObj->getId(), $productObj->getStore()->getWebsiteId());
        	$StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        	$qty                    = $StockState->execute($productObj->getSku());
        	$salable_qty            = $qty[0]['qty'];
        	$sourceList             = $objectManager->get('\Magento\Inventory\Model\ResourceModel\Source\Collection');
        	$sourceListArr          = $sourceList->load();
        	/*foreach ($sourceListArr as $sourceItemName) {
                	$sourceCode = $sourceItemName->getSourceCode();
                	$sourceName = $sourceItemName->getName();
		}*/
	}
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
</head>
<body id="main_body" >
-->	
	<img id="top" src="top.png" alt="">
	<div id="form_container">
	
		<h1><a>WMS Inward Form</a></h1>
		<form id="form_20409" class="appnitro"  method="post" action="wms_inwards_save.php">
					<div class="form_description">
			<h2>WMS Inward Form</h2>
			<p>Add or subtract quantity on a warehouse.</p>
		</div>						
	<ul >
			
					<li id="li_1" >
		<label class="description" for="challan_no">Challan No</label>
		<div>
			<input id="challan_no" name="challan_no" class="element text medium" type="text" maxlength="255" value=""/> 
		</div><p class="guidelines" id="guide_1"><small>Enter Challan number received from MC</small></p> 
		</li>		<li id="li_2" >
		<label class="description" for="challan_date">Challan Date</label>
		<input type="date" id="challan_date" name="challan_date">
		</li>		<li id="li_3" >
		<label class="description" for="invoice_no">Invoice Number</label>
		<div>
			<input id="invoice_no" name="invoice_no" class="element text medium" type="text" maxlength="255" value=""/> 
		</div> 
		</li>		<li id="li_4" >
		<label class="description" for="invoice_date">Invoice Date</label>
		<input type="date" id="invoice_date" name="invoice_date">
		</li>
		<li id="li_6" >
		<label class="description" for="supplier_payment_term">Payment term of invoice</label>
		<div>
                <div>
                        <select id="payment_term_drop" name="payment_term"  onchange="paymentTerm(this)">
                        <option value="0">Select Payment Term with MC</option>
                        <option value="1">Consignment</option>
                        <option value="2">Advance Payment</option>
                        <option value="3">Other</option>
			</select>
			<input id="payment_term_other" type="text" name="payment_term_other" maxlength="255" style="display:none;margin-top:10px;" />
                </div> 
		</div>
		<p class="guidelines" id="guide_6"><small>Select payment term with MC</small></p> 
		</li>		<li id="li_7" >
		<label class="description" for="supplier_purchase_no">Reference P.O number</label>
		<div>
			<input id="supplier_purchase_no" name="supplier_purchase_no" class="element text medium" type="text" maxlength="255" value=""/> 
		</div> 
		</li>		<li id="li_8" >
		<label class="description" for="sku">SKU</label>
		<div>
		<input id="sku_disabled" name="sku_disabled" class="element text medium" type="text" maxlength="255" value="<?php echo $sku ?>" readonly /> 
		<input id="sku" name="sku" type="hidden" value="<?php echo $sku ?>">
		</div> 
		</li>
		 <li id="li_5" >
                <label class="description" for="supplier_name">Supplier Name</label>
                <div>
                <input id="supplier_name" name="supplier_name" class="element text medium" type="text" maxlength="255" value="<?php echo $supplierName ?>" readonly />
                </div>
                </li>
		<li id="li_9" >
		<ul>
			<label class="description" for="existing_main_qty">Exisiting Main Qty Warehouse Wise : </label>
			<div>
			<?php
				$getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
				$AllInventoryBySources  = $getSourceItemsDataBySku->execute($sku);
				//print_r($AllInventoryBySources); die;
				$sourceCodeOptions = '';
        			foreach($AllInventoryBySources as $sourceInventory)
				{
					if($sourceInventory['source_code'] == 'CM-GGN')
					{	
						if($_SESSION['role'] == 'ALL-WH')
						{
							echo "<li><label class='description'>".$sourceInventory['name']."</label><input id='".$sourceInventory['source_code']."' name='".$sourceInventory['source_code']."' type='text' value='".$sourceInventory['quantity']."' readonly></li>";
							continue;
						}
						if($_SESSION['role'] == 'CM-GGN'){
							$sourceCodeOptions = "<option value='".$sourceInventory['source_code']."' ".$selected.">".$sourceInventory['name']."</option>";
                                        		echo "<li><label class='description'>".$sourceInventory['name']."</label><input id='".$sourceInventory['source_code']."' name='".$sourceInventory['source_code']."' type='text' value='".$sourceInventory['quantity']."' readonly></li>"; continue;
						}
						$selected = "selected";
					}
					$selected = "";
					 $sourceCodeOptions .= "<option value='".$sourceInventory['source_code']."' ".$selected.">".$sourceInventory['name']."</option>";
					echo "<li><label class='description'>".$sourceInventory['name']."</label><input id='".$sourceInventory['source_code']."' name='".$sourceInventory['source_code']."' type='text' value='".$sourceInventory['quantity']."' readonly></li>";
        			}
			?>
				<!--<input id="existing_main_qty" name="existing_main_qty" class="element text medium" type="text" maxlength="255" value="<?php echo $main_qty ?>" disabled/> -->
			</div>
			<div style="float:left;"><label class="description" for="existing_main_qty">Total Salable Qty in all WHs:</label>
			<input type="text" name="total_salable_qty" value="<?php echo $salable_qty ?>" readonly>
			 </div>
		</ul>
		</li>		
		<!--<li id="li_10" >
		<label class="description" for="existing_sale_qty">Existing Salable Qty </label>
		<div>
		<input id="existing_sale_qty" name="existing_sale_qty" class="element text small" type="text" maxlength="255" value="<?php echo $salable_qty ?>" disabled/> 
		</div> 
		</li>-->
		<li id="li_17">
                <label class="description" for="reduce_qty">Qty to be reduced</label>
                <div>
			<input id="reduce_qty" name="reduce_qty" class="element text small" type="text" maxlength="255" value="" onchange="disableME(this)" />
			<!--<input id="damage_reduce_qty" name="damage_reduce_qty" type="checkbox" />Damage/QC Rejection -->
			<input class="reduce_qty_radio" type="radio" id="damage_qc_rejection" name="reduce_qty_radio" value="damage_qc_rejection" onchange="checkRadioValue(this.value)">Damage / QC Rejection
			<input class="reduce_qty_radio" type="radio" id="goods_return" name="reduce_qty_radio" value="goods_return" onchange="checkRadioValue(this.value)">Goods Return
		<input id="debit_note" class="debit_note" type="text" name="Debit Note" style="display:none;float:right;" placeholder="Enter Debit Note"></input>
		</div>
		<p class="guidelines" id="guide_17"><small>Enter the Qty to be reduced</small></p>
                </li>
		<div class="add_qty_label" style="background: burlywood;">
		<li id="li_11" >
		<label class="description" for="qty_addition">Add Qty</label>
		<div>
			<input id="qty_addition" name="qty_addition" class="element text medium" type="text" maxlength="255" value="" onchange="disableME(this)"/> 
		</div><p class="guidelines" id="guide_11"><small>Enter the Quantity which is mentioned in MC Invoice.</small></p> 
		</li>
		<li id="li_18" >
                <label class="description" for="warehouse">Choose Warehouse</label>
                <div>
                <select class="element select medium" id="warehouse" name="warehouse" onchange="checkQtyAvailability()">
                <?php
                foreach ($sourceListArr as $sourceItemName) {
			$sourceCode = $sourceItemName->getSourceCode();
			if($sourceCode == 'default')continue;
			$sourceName = $sourceItemName->getName();
			if($sourceCode == 'CMWH')$selected = "selected";
			else{$selected = "";}
                        //echo "<option value='".$sourceCode."' ".$selected.">".$sourceName."</option>";
		}
				echo $sourceCodeOptions;
                ?>
                </select>
                </div><p class="guidelines" id="guide_18"><small>Choose Warehouse in which you want to add or subtrack the quantity.</small></p>
                </li>
		<li id="li_12" >
		<label class="description" for="qc_reject">QC Reject</label>
		<div>
			<input id="qc_reject" name="qc_reject" class="element text medium" type="text" maxlength="255" value="" onchange="qcReject(this)" /> 
		</div> 
		</li>		<li id="li_13">
		<label class="description" for="short_receipt">Short Receipt</label>
		<div>
			<input id="short_receipt" name="short_receipt" class="element text medium" type="text" maxlength="255" value="" onchange="shortReceipt(this)" /> 
		</div> 
		</li>		<li id="li_14">
		<label class="description" for="damages">Damages of warehouse</label>
		<div>
			<input id="damages" name="damages" class="element text medium" type="text" maxlength="255" value="" onchange="damage(this)" /> 
		</div> 
		</li>		<li id="li_15">
		<label class="description" for="inv_price_without_gst">Invoice Price without GST</label>
		<div>
			<input id="inv_price_without_gst" name="inv_price_without_gst" class="element text medium" type="text" maxlength="255" value=""/> 
		</div> 
		</li>		<li id="li_16" >
		<label class="description" for="gst">GST %</label>
		<div>
			<select name="gst">
			<option value="0">Select GST %</option>
			<option value="1">0%</option>
			<option value="3">3%</option>
			<option value="5">5%</option>
			<option value="12">12%</option>
			<option value="18">18%</option>
			<option value="28">28%</option>
			</select>
		</div> 
		</li>		
		</div>
		<li class="buttons">
			    <input type="hidden" name="form_id" value="20409" />	    
				<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" onClick="return warehouseSelection();"/>
		</li>
	</ul>
	</form>	
	</div>
	<img id="bottom" src="bottom.png" alt="">
	</body>
</html>
<script>
function checkRadioValue(value)
{
	if(value == 'goods_return')
		document.getElementById("debit_note").style.display = "block";
	else document.getElementById("debit_note").style.display = "none";
}
function checkQtyAvailability()
{
	var warehouse           = document.getElementById("warehouse").value;
	var reduced_qty_value	= document.getElementById('reduce_qty').value;
	if(warehouse=='CM-GGN')
	{
		document.getElementById('damage_qc_rejection').disabled = false;
                document.getElementById('goods_return').disabled = false;
		 document.getElementById('qc_reject').disabled = false;
                document.getElementById('short_receipt').disabled = false;
                document.getElementById('damages').disabled = false;
	}
	if(warehouse!='CM-GGN')
        {
                document.getElementById('damage_qc_rejection').disabled = true;
		document.getElementById('goods_return').disabled = true;
		document.getElementById('damage_qc_rejection').checked = false;
		document.getElementById('goods_return').checked = false;
		 document.getElementById('qc_reject').disabled = true;
                document.getElementById('short_receipt').disabled = true;
                document.getElementById('damages').disabled = true;
        }

}
function warehouseSelection()
{
	var warehouse 		= document.getElementById("warehouse").value;
	var salableQty          = parseInt(document.getElementById(warehouse).value);
	var challan_no 		= document.getElementById("challan_no").value;
	var challan_date        = document.getElementById("challan_date").value;
	var invoice_no 		= document.getElementById("invoice_no").value;
	var invoice_date        = document.getElementById("invoice_date").value;
	var qtyAdditionValue 	= parseInt(document.getElementById('qty_addition').value);
        var qcRejectValue 	= parseInt(document.getElementById('qc_reject').value);
        var shortReceiptValue 	= parseInt(document.getElementById('short_receipt').value);
	var damagesValue 	= parseInt(document.getElementById('damages').value);
	var reduceQtyValue	= parseInt(document.getElementById('reduce_qty').value);
	var goods_returned     	= document.getElementById('goods_return').checked;
	var damage_qc_rejection = document.getElementById('damage_qc_rejection').checked;
	var debit_note_value	= document.getElementById('debit_note').value;
	//var radiobuttonValue 	= 
	//var damageCheck = damageCheckbox;
	//alert(damageCheckbox);
	//var damageCheck     	= parseInt(document.getElementById('damage_reduce_qty').checked);
	if(qtyAdditionValue < (qcRejectValue+shortReceiptValue+damagesValue))
	{
		//alert(qtyAdditionValue);
		//alert(qcRejectValue);
		//alert(shortReceiptValue);
		//alert(damagesValue);
		alert("Total qty(Qc reject + short receipt + damages) can not more than the addition qty");
		return false;
	}
	if(warehouse == 'CM-GGN' && challan_no=='' && invoice_no == ''){
		if(qtyAdditionValue){
			alert("Either Challan no or Invoice no is required for CMWH Gurgaon warehouse."); return false;
		}
		if(reduceQtyValue && goods_returned && debit_note_value == ''){
			alert("Debit note is required in case of goods return."); return false;
		}
		if(reduceQtyValue && !damage_qc_rejection && !goods_returned)
		{
			alert("Inventory for CM warehouse can only be reduced in case of QC reject, damages or in case of good returns."); return false;	
		}
		
	}
		
	if(warehouse == 'CM-GGN' && challan_no != '' && challan_date == '')
	{
		alert("Challan date is also required");
		return false;
	}
	 if(warehouse == 'CMWH' && invoice_no != '' && invoice_date == '')
        {
                alert("Invoice date is also required");
                return false;
        }
	if(qtyAdditionValue=='' && reduceQtyValue=='' || qtyAdditionValue<=0)
	{
		alert("Either add qty or reduce qty field is required.");
		return false;
	}
	if(isNaN(qtyAdditionValue) && isNaN(reduceQtyValue))
        {
                alert("Either add or reduce qty must have a number.");
                return false;
        }
	if(reduceQtyValue > salableQty){ alert("reduce qty can not be greater than total qty of selected Warehouse."); return false;}
	else{ return true;}
}
function disableME(ele)
{

	var currentElementId 	= ele.id;
      	var qtyAdditionTextBox 	= document.getElementById('qty_addition');
	var qcRejectTextBox 	= document.getElementById('qc_reject');
	var shortReceiptTextBox = document.getElementById('short_receipt');
	var damagesTextBox 	= document.getElementById('damages');
	var reduceQtyTextBox 	= document.getElementById('reduce_qty');
	var warehouse           = document.getElementById("warehouse").value;
	var salableQty 		= parseInt(document.getElementById(warehouse).value);
	var totalSalableQty	= parseInt("<?php echo $salable_qty ?>");

  if (currentElementId == 'reduce_qty' && ele.value != '')	
  {
	  if( parseInt(ele.value) == 0)
	  {
		alert("Reduce qty should be greater than 0.");
                  ele.value = '';
	  }
	  if(parseInt(salableQty) < parseInt(ele.value))
	  {
		  alert("You can only reduce "+salableQty+" qty which is currently avaialable in your selected warehouse");
		  ele.value = '';
	  }
	  
	  if(parseInt(totalSalableQty) < parseInt(ele.value))
          {
                  alert("Reduce qty can not be greater than total salable qty");
                  ele.value = '';
          }
	  if(warehouse!='CM-GGN')
	  {
	  	document.getElementById('damage_qc_rejection').disabled = true;
		document.getElementById('goods_return').disabled = true;
		document.getElementById('qc_reject').disabled = true;
		document.getElementById('short_receipt').disabled = true;
		document.getElementById('damages').disabled = true;
	  }
	   if(warehouse=='CM-GGN')
          {
                //document.getElementById('damage_qc_rejection').disabled = true;
                //document.getElementById('goods_return').disabled = true;
                document.getElementById('qc_reject').disabled = true;
                document.getElementById('short_receipt').disabled = true;
                document.getElementById('damages').disabled = true;
          }

    qtyAdditionTextBox.value	= "";
    qtyAdditionTextBox.disabled = true;
    
    qcRejectTextBox.value	= "";
    qcRejectTextBox.disabled 	= true;
    
    shortReceiptTextBox.value		= "";
    shortReceiptTextBox.disabled 	= true;
    
    damagesTextBox.value	= "";
    damagesTextBox.disabled 	= true;
    reduceQtyTextBox.disabled 	= false;
  }
  if ((currentElementId == 'qty_addition'  || currentElementId == 'reduce_qty') && ele.value == '')
  {
    reduceQtyTextBox.disabled           = false;
    qtyAdditionTextBox.disabled         = false;
    qcRejectTextBox.disabled            = false;
    shortReceiptTextBox.disabled        = false;
    damagesTextBox.disabled             = false;
  }

  if(currentElementId == 'qty_addition' && ele.value != '')
  {
    reduceQtyTextBox.value		= "";
    reduceQtyTextBox.disabled 		= true;
    qtyAdditionTextBox.disabled 	= false;
    qcRejectTextBox.disabled 		= false;
    shortReceiptTextBox.disabled 	= false;
    damagesTextBox.disabled 		= false;
	if(warehouse!='CM-GGN')
          {
                document.getElementById('damage_qc_rejection').disabled = true;
                document.getElementById('goods_return').disabled = true;
                document.getElementById('qc_reject').disabled = true;
                document.getElementById('short_receipt').disabled = true;
                document.getElementById('damages').disabled = true;
          }
           if(warehouse=='CM-GGN')
          {
                //document.getElementById('damage_qc_rejection').disabled = true;
                //document.getElementById('goods_return').disabled = true;
                document.getElementById('qc_reject').disabled = false;
                document.getElementById('short_receipt').disabled = false;
                document.getElementById('damages').disabled = false;
          }
	
  }
}
function paymentTerm(element)
{
	var end = element.value;
	if(end==3){document.getElementById("payment_term_other").style.display = "block";}
	else{document.getElementById("payment_term_other").style.display = "none"; }
}
function qcReject(element)
{
	var qtyAdditionValue = parseInt(document.getElementById('qty_addition').value);
	if(parseInt(element.value) > qtyAdditionValue)
	{
		alert("QC Reject can not greater than Qty addition");
		document.getElementById('qc_reject').value = '';
	}
}
function shortReceipt(element)
{
        var qtyAdditionValue = parseInt(document.getElementById('qty_addition').value);
        if(parseInt(element.value) > qtyAdditionValue)
        {
                alert("Short Receipt can not greater than Qty addition");
                document.getElementById('short_receipt').value = '';

        }
}
function damage(element)
{
        var qtyAdditionValue = parseInt(document.getElementById('qty_addition').value);
        if(parseInt(element.value) > qtyAdditionValue)
        {
                alert("Damages can not greater than Qty addition");
                document.getElementById('damages').value = '';
        }
}
</script>
<?php 
//}
//else{die("Please select sku to continue...");}
?>
