<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$file = fopen('stock_sources_20210430_213256_new_updated.csv', 'r');
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$stock_sources_table = $resource->getTableName('stock_sources_table'); //gives table name with prefix
$i=0;
while (($line = fgetcsv($file)) !== FALSE) {
        //$line[0] = '1004000018' in first iteration
        if($i==0){$line[4] = 'Product Status'; $line[5] = 'Product Name';}
        else{
                $productRepository      = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
		if(strpos($line[1], '-1') !== false){
			if($line[1] != 'CN1BGCLLS0007-1' || $line[1] != 'CN1BGCLLS0004-1' || $line[1]!='CN1BGCLLS0001-1')continue;
		}
		if(strpos($line[1], 'CMPCL1BPRGJR00') !== false)continue;
		if(strpos($line[1], 'CMPCL1BPRGT') !== false)continue;
		if(strpos($line[1], 'CMPCN1BGAPM') !== false)continue;
		if(strpos($line[1], 'CMPFC1KAAKS') !== false)
		{
			if($line[1] != 'CMPFC1KAAKSO0003' || $line[1] != 'CMPFC1KAAKSO0004' || $line[1]!='CMPFC1KAAKSO0005')continue;
		}
		if(strpos($line[1], 'CMP') !== false)continue;
		if(strpos($line[1], '-2') !== false)continue;
		$missingSKU = array("WL1HWGCST0007","CMPBR1BESRPW0028","FC1BGRCSE00721","CMPWL1HWGCSO0003","CMPNG1PMZBTL0042","CMPBR1BESRDI0005","CMPBR1BESRDI0015","CMPWL1HWGCSO0007","CMPBR1BESRCH0001","CMPNG1PMZBTL0035","CMPBR1BESRDI0017","CMPBR1BESRDI0004","CMPCN1BGAPCR0115","NG1PMZBCT0010","NG1PMZBCT0011","CN1BGCLLS0001-","CN1BGCLLS0002-","CN1BGCLLS0003-","main-s-p","CN1BGCLLS0004-","CN1BGCLLS0007-","CN1BGCLLS0001-n1","pashminastole01","CN1BGAPTC0148-9","ML1AJBTST0144");
		if(in_array($line[1], $missingSKU))continue;
		echo $line[1]."\n";
		$productObj             = $productRepository->get($line[1]);
		$insertData = [
				'sku' 	=> $line[1],
				'name' 	=> $productObj->getName(),
				'status' => $productObj->getStatus(),
				'quantity' => $line[3]
				];
		$connection->insert($stock_sources_table, $insertData);
                //echo $productObj->getName();
                //$line[4] = $productObj->getStatus();
                //$line[5] = $productObj->getName();
        }
        //print_r($line);
        $i++;
}
fclose($file);
