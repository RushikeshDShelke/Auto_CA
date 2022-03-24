<?php
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
//print_r($_REQUEST);

if(isset($_REQUEST['id']) && $_REQUEST['id'])
{
	$status = 'requested_for_approval';
	if(isset($_REQUEST['status']) && $_REQUEST['status'])$status = $_REQUEST['status'];
	$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
	$connection     = $resource->getConnection();
	$po_order_data  = $resource->getTableName('po_order_data');
	$sql 		= "update ".$po_order_data." set status='".$status."' where id=". $_REQUEST['id'];
	$connection->query($sql);
	/*$to = ['manojt095@gmail.com', 'cm.techlead@craftmaestros.com'];
                $from = 'manojt095@gmail.com';
                $nameFrom = $nameTo= 'Manoj Taneja';
                //$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
                //$UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
		//$body = $SubmitForApprovalUrl = "Please click <a href='".$baseUrl."wms/wms_preview.php?".$UrlParamsArray[1]."&maillink=true&id=".$lastInsertedId."'>Here</a> to review the purchase order.";
		$body = "PO has been ".$status;
                $email = new \Zend_Mail();
                $email->setSubject("PO No: CM".date("y")."00".$_REQUEST['id']." : ".$status);
                $email->setBodyHtml($body);
                $email->setFrom($from, $nameFrom);
                $email->addTo($to, $nameTo);
                $email->send(); */
	try {
	$authDetails = array(
                'ssl' => 'ssl',
                'port' => 465,  //or 465
                'auth' => 'login',
                'username' => 'care@craftmaestros.com',
                'password' => 'CMcares@you'
        );
        $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $authDetails);
        Zend_Mail::setDefaultTransport($transport);
        //$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        //$UrlParamsArray = explode("?",$urlInterface->getCurrentUrl());
        //$body = $SubmitForApprovalUrl = "Please click <a href='".$baseUrl."wms/wms_preview.php?".$UrlParamsArray[1]."&maillink=true&id=".$lastInsertedId."'>Here</a> to review the purchase order.";
	$body = "PO has been ".$status;
        $mail = new Zend_Mail();
        $mail->setBodyHtml($body);
        $mail->setFrom('care@craftmaestros.com', 'Craft Maestros');
        $mail->addTo(['yatin.dhingra@craftmaestros.com', 'sugandha.jain@craftmaestros.com', 'cm.techlead@craftmaestros.com'], 'Craft Maestros');
        $mail->setSubject("PO No: CM".date("y")."00".$_REQUEST['id']." : ".$status);
        $mail->send();

        } catch (Zend_Exception $e) {
                echo $e->getMessage(); exit;
        }

	echo "You have ".$status." the PO successfully";	
}
?>
