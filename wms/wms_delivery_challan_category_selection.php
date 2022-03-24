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
$objectManager = $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
$categories = $categoryFactory->create()
    ->addAttributeToSelect('*')->setStore($storeManager->getStore());
echo "<form name='caetgory_selection' method='post' action='wms_delivery_challan_generation.php'><h3>Select Categories</h3>";
foreach ($categories as $category){
?>
<input type="checkbox" name="<?php echo $category->getId()?>" id="<?php echo $category->getId()?>" value='<?php echo $category->getId()?>'><?php echo $category->getName() ?></br>
<?php }
echo "<input type='submit'></form>";
?>
