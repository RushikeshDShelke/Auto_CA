<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
if(isset($_POST["import"])) {
$fileName = $_FILES["file"]["tmp_name"];
  if ($_FILES["file"]["size"] > 0) {
		if (($fh = fopen($fileName, "r")) !== FALSE) {
        $i = 0;
        while (($items = fgetcsv($fh, 10000, ",")) !== FALSE) {
          if($i == 0){ // skip the first row if there is a tile row in CSV file
              $i++;
              continue;
          }
	echo $items[0]."<br>";
	 $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
$productObj = $productRepository->get($items[0]);
$stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
$stockItem = $stockRegistry->getStockItemBySku($items[0]);
    $stockItem->setQty($items[1]);
    $stockItem->setIsInStock((bool)$items[1]); // this line
    $stockRegistry->updateStockItemBySku($items[0], $stockItem);
            $i++;
        }
    }
	}
$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
$productObj = $productRepository->get('CL1BPRGHK0006');
$stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
$stockItem = $stockRegistry->getStockItemBySku($items[0]);
    $stockItem->setQty($items[1]);
    $stockItem->setIsInStock((bool)$items[1]); // this line
    $this->stockRegistry->updateStockItemBySku($items[0], $stockItem);
}
?>
<form class="form-horizontal" action="" method="post" name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
  <div class="input-row">
      <label class="col-md-4 control-label">Choose CSV
          File</label> <input type="file" name="file"
          id="file" accept=".csv">
      <button type="submit" id="submit" name="import"
          class="btn-submit">Import</button>
      <br />
  </div>
</form>
