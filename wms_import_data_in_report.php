<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
require __DIR__ . '/app/bootstrap.php';
$params         = $_SERVER;
$bootstrap      = Bootstrap::create(BP, $params);
$objectManager  = $bootstrap->getObjectManager();
$state          = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager   = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl        = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$tableName      = $resource->getTableName('wms_users'); //gives table name with prefix
$wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
$file = fopen('Uploaded_sheet_WMS_final.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {
 	if($i==0){ $i++; continue;}
	else{
		$insertData = [
            'challan_number' =>'',
            'challan_date' => '',
            'invoice_number' => '',
            'invoice_date' => '',
            'payment_term' => '',
            'payment_term_other' =>'',
            'ref_purchase_no'=>'',
            'supplier_name'=> '',
            'sku' => $line[1],
            'CMWH_existing'=> '',
            'MTWH_existing'=> '',
            'total_salable_qty'=> '',
            'updated_qty' => $line[3],
            'warehouse'=> $line[0],
            'qty_addition'=>$line[3],
            'qc_reject'=> 0,
            'short_receipt'=>0,
            'damage_warehouse'=>0,
            'reduce_qty'=>'',
            'goods_return'=>'',
            'debit_note'=>'',
            'invoice_price_without_gst'=> '',
            'gst_percentage'=> '',
            'created_at'=> date("Y-m-d h:i:s")
    ];
        $connection->insert($wms_inward_table, $insertData);
	}
	$i++;
}
fclose($file);
