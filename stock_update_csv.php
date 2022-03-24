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
$file = fopen('stock_sources_20210430_213256_new.csv', 'r');
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$i=0;
while (($line = fgetcsv($file)) !== FALSE) {
        if($i==0){$line[4] = 'Product Status'; $line[5] = 'Product Name';}
        else{
                $productRepository      = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                $productObj             = $productRepository->get($line[1]);
                //echo $productObj->getName();
                $line[4] = $productObj->getStatus();
                $line[5] = $productObj->getName();
        }
        $i++;
}
fclose($file);
