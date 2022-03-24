<?php 
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

$collection = $productCollection->create()
        ->addAttributeToSelect('*')
        //->addAttributeToSelect('name')
            ->load();
echo "<table>";
foreach($collection as $item)
{
$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                        $productObj = $productRepository->get($item->getSku());
 $ArtisantaxValueArray = array("5"=>3,
                                        "6"=>5,
                                        "7"=>12,
                                        "8"=>18,
                                        "9"=>28
                                );
                                $productTaxPercentage = 0;
                                if (array_key_exists($productObj->getTaxClassId(),$ArtisantaxValueArray))
                                {
                                        $productTaxPercentage = $ArtisantaxValueArray[$productObj->getTaxClassId()];
                                }
                                else{$productTaxPercentage; }
$gstValue = ((int)$item->getData()['price'] * (int)$productTaxPercentage)/100;
$final_price = (int)$item->getData()['price'] + $gstValue;				
        //$productNameOptions .= "<option value='".$item->getData()['sku']."'>".$item->getData()['name']."</optoin>";
        //echo "<option value='".$item->getData()['sku']."'>".$item->getData()['sku']."</optoin>";
	echo "<tr><td>".$item->getData()['sku']."</td><td>".$final_price."</td><td>".$item->getData()['name']."</td></tr>";
}
echo "</table>";
