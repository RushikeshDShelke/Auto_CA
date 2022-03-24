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
$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
//echo "<pre>"; print_r($_REQUEST); die;
if($_REQUEST)
{
	if(isset($_REQUEST['supplier_id']) && !empty($_REQUEST['supplier_id']))
        {
		$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($_REQUEST['supplier_id']);
		$billingAddressId = $customer->getDefaultBilling();
		$address = $objectManager->get('Magento\Customer\Model\AddressFactory')->create()->load($billingAddressId);
		//echo "<pre>";print_r($customer->getData());
		//die;
        }
?>
<style>
table, th, td {
    /* padding: 10px; */
    border: 1px solid black;
    /*border-collapse: collapse; */
}
</style>
<?php
	/*if(array_key_exists("preview",$_REQUEST) && $_REQUEST['preview'] && !array_key_exists("maillink",$_REQUEST))
	{
		$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
		$UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
		$SubmitForApprovalUrl = $baseUrl."wms/wms_PO_orderData.php?".$UrlParamsArray[1];
		$returnUrl = "?supplier_list=".$_REQUEST['supplier_id'];
		echo "<div style='float:left;'><a href='".$baseUrl."wms/wms_PO_inwards.php".$returnUrl."'>GO Back </a></div><div style='float:left;'> || <a href='".$SubmitForApprovalUrl."'>Submit for approval</a></div>";
	}
	if(array_key_exists("maillink",$_REQUEST))
	{
		$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                $UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
                $SubmitForApprovalUrl = $baseUrl."wms/wms_PO_approve.php?".$UrlParamsArray[1];
		echo "<div style='float:left;'><a href='".$SubmitForApprovalUrl."&status=rejected'>Reject </a></div><div style='float:left;'> || <a href='".$SubmitForApprovalUrl."&status=approved'>Approve</a></div>";
	}*/
	echo "<table width='100%'><tr><th colspan='12'>Production Order</th></tr>";
	echo "<tr><td colspan='12'>Purchase Order Date : ".date("Y-m-d h:i:s")."</td></tr>"; ?>
	<tr>
	<td colspan='2' width="100%">
		<table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tbody><tr>
						<td>Vendor Details: </td>
                                                </tr>
                                                <tr>
                                                    <td>Name: <?php echo $customer->getName(); ?> </td> 
                                                </tr>
                                                <tr>
						<td>Address: <?php echo $address->getData('street').", ".$address->getData('city').", ".$address->getData('region').", ".$address->getData('postcode') ?></td>
                                                </tr>
                                                <tr>
						<td>Phone: <?php echo $customer->getData()['contact1']; ?></td>
                                                </tr>
                                                <tr>
						<td>GST: <?php if(array_key_exists("customer_gst",$customer->getData()))echo $customer->getData()['customer_gst']; ?></td>
                                                </tr>
                                            </tbody></table>
	</td>
	<td colspan='2'></td>
	<td colspan='8' width="330" style="padding: 10px 10px 10px 20px;">
                                            <h2> <b>AMMARA CRAFT MAESTROS PVT. LTD</b>
                                            </h2>
                                            <p>
                                                B-1802, Pioneer Park,
                                            </p>
                                            <p>
                                                Golf Course extn. Road Secotor 61, Gurgaon 122001, Haryana, India.
                                            </p>
                                            <ul>
                                                <li> <span>Tel :</span> <a href="tel:+911244307447">+91
                                                                124-4307447;</a> </li>
                                                <li><span> Email :</span> <a href="mailto:
                                                                accounts@craftmaestros.com">
                                                                accounts@craftmaestros.com</a></li>
                                            </ul>
                                            <strong>
                                                        GSTN: 06AARCA2483K1Z9
                                                    </strong>
					</td>
	</tr>
	<tr>
	<td colspan='12'>Payment Term: <?php if(isset($_REQUEST['payment_term']))echo $_REQUEST['payment_term']; ?></td>
	</tr>
	<tr>
	<td colspan='12'>Freight Term: <?php if(isset($_REQUEST['freight_term']))echo $_REQUEST['freight_term']; ?></td>
        </tr>
	<tr>
        <td colspan='12'>Purchase Order No: <?php if(isset($_REQUEST['po_no'])) {echo $_REQUEST['po_no'];} else{ ?>CM2100✖✖ <?php } ?></td>
	</tr>
	<tr>
        <td colspan='12'>Delivery Address : Ammara craft maestros Pvt .Ltd, C-1524 Basement Block C Sushant Lock Phase 1 Haryana 122101</td>
        </tr>
	<tr>
	<td>Sr.No</td>
	<td>Image</td>
	<td>Name</td>
	<td>Product Type</td>
	<td>Size</td>
	<td>SKU</td>
	<td>Quantity</td>
	<td>Ready Date</td>
	<td>Price without GST</td>
	<td>GST %</td>
	<td>GST Value</td>
	<td>Row Total</td>
	</tr>
	
<?php
	$sr_counter =0;
	$row_total = 0;
	$subtotal = 0;
	$returnUrl = "?supplier_list=".$_REQUEST['supplier_id'];
	if(isset($_REQUEST['payment_term']))$returnUrl = $returnUrl."&payment_term=".$_REQUEST['payment_term'];
	if(isset($_REQUEST['freight_term']))$returnUrl = $returnUrl."&freight_term=".$_REQUEST['freight_term'];
	foreach($_REQUEST as $key => $value)
	{
		if($key != 'supplier_id' && $key != 'payment_term' && $key != 'freight_term' && $key!='preview' && $key!='payment_term_other' && $key!='maillink' && $key!='id' && $key != 'po_no')
		{
			if(strpos($key, "_date") === false && $value)
			{
			$sr_counter = $sr_counter + 1;
			$returnUrl = $returnUrl."&".$key."=".$value."&".$key."_date=".$_REQUEST[$key."_date"];
			$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
			$productObj = $productRepository->get($key);
			//echo $productObj->getName();

	?>
		<tr>
			<td><?php echo $sr_counter ?></td>
			<td><?php echo "<img width='200px' height='200px' src='".$mediaUrl."catalog/product".$productObj->getData('image')."' />" ?></td>
			<td><?php echo $productObj->getName()?></td>
			<td><?php echo $productObj->getTypeId() ?></td>
			<td><?php echo $productObj->getSize() ?></td>
			<td><?php echo $key ?></td>
			<td><?php echo $value ?></td>
			<td><?php echo $_REQUEST[$key."_date"] ?></td>
			<td><?php echo $productObj->getMarketplaceFee() ?></td>
			<td><?php 
				$ArtisantaxValueArray = array("103"=>3,
                                        "104"=>5,
                                        "105"=>12,
                                        "106"=>18,
                                        "107"=>28
                                );
                		$productTaxPercentage = 0;
                		if (array_key_exists($productObj->getArtisanGst(),$ArtisantaxValueArray))
                		{
                        		echo $productTaxPercentage = $ArtisantaxValueArray[$productObj->getArtisanGst()];
                		}
				else{echo $productTaxPercentage; }
			 ?></td>
			<td><?php echo $gstValue = ((int)$productObj->getMarketplaceFee() * (int)$productTaxPercentage)/100; ?></td>
			<td><?php echo $row_total = (((int)$productObj->getMarketplaceFee() + (int)$gstValue)* (int)$value); $subtotal =  $subtotal +  $row_total; ?></td>
		</tr>
<?php }}
	}?>
	<tr><td colspan="11">Subtotal</td><td><?php echo $subtotal  ?></td></tr></table>
<?php 
/* if(array_key_exists("preview",$_REQUEST) && $_REQUEST['preview'] && !array_key_exists("maillink",$_REQUEST))
        {
                $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                $UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
                $SubmitForApprovalUrl = $baseUrl."wms/wms_PO_orderData.php?".$UrlParamsArray[1];
                //$returnUrl = "?supplier_list=".$_REQUEST['supplier_id'];
                echo "<div style='float:left;padding:50px;'><a href='".$baseUrl."wms/wms_PO_inwards.php".$returnUrl."'>GO Back </a></div><div style='float:left;padding:50px'><a href='".$SubmitForApprovalUrl."'>Submit for approval</a></div>";
        }
        if(array_key_exists("maillink",$_REQUEST))
        {
                $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                $UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
                $SubmitForApprovalUrl = $baseUrl."wms/wms_PO_approve.php?".$UrlParamsArray[1];
                echo "<div style='float:left;padding:50px'><a href='".$SubmitForApprovalUrl."&status=rejected'>Reject </a></div><div style='float:left;padding:50px;'><a href='".$SubmitForApprovalUrl."&status=approved'>Approve</a></div>";
        }
*/
}

?>
<script>
window.print();
</script>
