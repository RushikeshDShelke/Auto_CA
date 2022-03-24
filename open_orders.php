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
$wms_inward_table = $resource->getTableName('inventory_reservation'); //gives table name with prefix
$file = fopen('final_open_orders.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {
 	if($i==0){ $i++; continue;}
	else{
		$insertData = [
            'stock_id' =>2,
            'sku' => $line[2],
            'quantity' => '-'.$line[3],
            'metadata' => '{"event_type":"order_placed","object_type":"order","object_id":"'.$line[0].'"}'
    ];
        echo $connection->insert($wms_inward_table, $insertData);
	}
	$i++;
}
fclose($file);
