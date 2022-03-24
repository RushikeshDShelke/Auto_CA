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
        echo "<div class='logout'><a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a><a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a><a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a> <a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a>";
	echo "<a href='".$baseUrl."wms/wms_delivery_challan_generation.php'>Delivery Challan Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_delivery_challan_list.php'>Delivery Challan List || </a>";
	echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
                                echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
        echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
//echo "<pre>";print_r($_REQUEST); die;
if($_REQUEST)
{
	$supplier_id = $_REQUEST['supplier_id'];
	$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($supplier_id);
	$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
	$connection     = $resource->getConnection();
	$po_order_data  = $resource->getTableName('po_order_data'); //gives table name with prefix
	$po_order_item  = $resource->getTableName('po_order_item');
	$payment_term = '';
	$freight_term = '';
	if(isset($_REQUEST['payment_term']) && $_REQUEST['payment_term'])
	{
		$payment_term = $_REQUEST['payment_term'];
		if($_REQUEST['payment_term'] == 'other')$payment_term = $_REQUEST['payment_term_other'];
	}
	if(isset($_REQUEST['freight_term']) && $_REQUEST['freight_term'])
        {
		$freight_term = $_REQUEST['freight_term'];
	}
	$insertData 	= ["status"=>"requested_for_approval",
			"po_no"=>"CM".date("y")."00",
			"email_sent"=> 0,
			"payment_term"=> $payment_term,
			"freight_term" => $freight_term,
		   "supplier_id"=>$supplier_id,
		   "created_at" =>date("Y-m-d h:i:s"),
	   	   "created_by" => $_SESSION['username']
	];
	$status = 'requested_for_approval';
	$po_no = "CM".date("y")."00";
	$email_sent = 0;
	
	//Update Data into table
	$sql = "Update ".$po_order_data." Set status = 'closed' where id = ".str_replace($po_no, '', $_REQUEST['po_no']);
	$connection->query($sql);
	
	//$result = $connection->insert($po_order_data, $insertData);
	//$lastInsertedId = str_replace($po_no, '', $_REQUEST['po_no']);
	//if($result)$lastInsertedId = $connection->lastInsertId();
	$lastInsertedId = 0;
	if($lastInsertedId)
	{
		/* $to = ['manojt095@gmail.com', 'cm.techlead@craftmaestros.com'];
		$from = 'care@craftmaestros.com';
		$nameFrom = $nameTo= 'Manoj Taneja';
		$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                $UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
                $body = $SubmitForApprovalUrl = "Please click <a href='".$baseUrl."wms/wms_preview.php?".$UrlParamsArray[1]."&maillink=true&id=".$lastInsertedId."'>Here</a> to review the purchase order.";
    		$email = new \Zend_Mail();
    		$email->setSubject("Review PO No: CM".date("y")."00".$lastInsertedId);
    		$email->setBodyHtml($body);
    		$email->setFrom($from, $nameFrom);
    		$email->addTo($to, $nameTo);
    		$email->send(); */
		/* try {
        $authDetails = array(
                'ssl' => 'ssl',
                'port' => 465,  //or 465
                'auth' => 'login',
                'username' => 'care@craftmaestros.com',
                'password' => 'CMcares@you'
        );
        $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $authDetails);
        Zend_Mail::setDefaultTransport($transport);
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
        $body = $SubmitForApprovalUrl = "Please click <a href='".$baseUrl."wms/wms_preview.php?".$UrlParamsArray[1]."&maillink=true&id=".$lastInsertedId."'>Here</a> to review the purchase order.";
        $mail = new Zend_Mail();
        $mail->setBodyHtml($body);
        $mail->setFrom('care@craftmaestros.com', 'Craft Maestros');
	$mail->addTo(['akhil.kathuria@craftmaestros.com', 'cm.techlead@craftmaestros.com', 'akansha@craftmaestros.com'], 'Craft Maestros');
	//$mail->addTo(['cm.techlead@craftmaestros.com'], 'Craft Maestros');
        $mail->setSubject("Review Updated PO No: CM".date("y")."00".$lastInsertedId);
        $mail->send();
       
        } catch (Zend_Exception $e) {
        	echo $e->getMessage(); exit;
	}*/
	}
	/*foreach($_REQUEST as $key => $value)
	{
		if($key != 'supplier_id' && $key != 'payment_term' && $key != 'freight_term' && $key!='preview' && $key != 'payment_term_other' &&  $key != 'maillink' &&  $key != 'id' && $key != 'po_no')
		{
			if($value)
			{
                        if(strpos($key, "_date") === false)
			{
				//$connection->insert($po_order_item, ["po_no"=>$lastInsertedId,"sku"=>$key,"qty_ordered"=>$value,"ready_date"=>$_REQUEST[$key."_date"], "created_at"=>date("Y-m-d h:i:s"),"created_by"=>$_SESSION['username']]);
				$selectSql = "select * from ".$po_order_item." where sku='".$key."' and po_no='".$lastInsertedId."'";
				$result = $connection->fetchAll($selectSql);
				if($result)
				{
				//Update Data into table
        			$sql = "Update ".$po_order_item." Set qty_ordered = '".$value."',ready_date='".$_REQUEST[$key."_date"]."',created_at='".date("Y-m-d h:i:s")."',created_by='".$_SESSION['username']."' where po_no = '".$lastInsertedId."' and sku = '".$key."'";
        			$connection->query($sql);
				echo "Your PO has been saved and sent for approval. You will be notified once approver approves or rejects. </br>";
				}
				else{
						$connection->insert($po_order_item, ["po_no"=>$lastInsertedId,"sku"=>$key,"qty_ordered"=>$value,"ready_date"=>$_REQUEST[$key."_date"], "created_at"=>date("Y-m-d h:i:s"),"created_by"=>$_SESSION['username']]);
				}
			}
			}
		}

	//	print_r($value); die;
		if($value)
		{
			if(array_key_exists($key."_qty",$_REQUEST))
			{
				if($_REQUEST[$key."_qty"])
				{
					$qty_ordered = $_REQUEST[$key."_qty"];
					$connection->insert($po_order_item, ["po_no"=>$lastInsertedId,"sku"=>$key,"qty_ordered"=>$qty_ordered,"ready_date"=>$_REQUEST[$key."_date"], "created_at"=>date("Y-m-d h:i:s"),"created_by"=>$_SESSION['username']]);
					echo "Your PO has been saved successfully. </br>";
				}
			}
		}
}*/
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
<th>created_at</th>
<th>created_by</th>
</tr>
<?php
$sql = "Select * FROM " .$po_order_data. " where status != 'closed' ";
$result = $connection->fetchAll($sql);
if($result)
{
	foreach($result as $row)
	{
		echo "<tr><td>".$row['po_no']."".$row['id']."</td><td>".$row['status']."</td><td>".$row['supplier_id']."</td><td>".$row['payment_term']."</td><td>".$row['created_at']."</td><td>".$row['created_by']."</td></tr>";
	}
}
?>
</table>
