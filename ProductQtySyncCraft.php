<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
if (isset($_REQUEST['sku']) && isset($_REQUEST['salableqty']) && isset($_REQUEST['mainqty'])) {
        //$myfile                 = fopen("/var/www/html/testfile.txt", "w"); fwrite($myfile, $_REQUEST['qty']);
        $productRepository      = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $productObj             = $productRepository->get($_REQUEST['sku']);
	$StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $qty                    = $StockState->execute($productObj->getSku());
        $total_salable_qty_CM   = $qty[0]['qty'];
	$toal_salable_qty_EF 	= $_REQUEST['qty'];
	$UpdatedQty = $QtyToBeSynced = ((int)$total_salable_qty_CM - (int)$toal_salable_qty_EF);
 	$getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
        $AllInventoryBySources  = $getSourceItemsDataBySku->execute($_REQUEST['sku']);
        foreach($AllInventoryBySources as $sourceInventory)
        {
        	if($sourceInventory['source_code'] == 'CM-GGN')
               	{
			if($sourceInventory['quantity']>= $UpdatedQty)
			{
				$netQtyToBeAdded = $reducedQty = (int)$UpdatedQty;
				$UpdatedQty = ((int)$sourceInventory['quantity'] - (int)$UpdatedQty);
				$sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
        			$sourceItem		= $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
        			$sourceItem->setSourceCode('CM-GGN');
        			$sourceItem->setSku($_REQUEST['sku']);
        			$sourceItem->setQuantity($UpdatedQty);
        			$sourceItem->setStatus(1);
        			$sourceItemInterface->execute([$sourceItem]);
				//$netQtyToBeAdded = intval($_REQUEST['reduce_qty']);
				$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection     = $resource->getConnection();
				$tableName      = $resource->getTableName('wms_users'); //gives table name with prefix
				$wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
                		$sql = "select * from ".$wms_inward_table." where sku='".$_REQUEST['sku']."' and warehouse='CM-GGN' and qty_addition!='' and qty_addition!=0 and updated_qty!='' and updated_qty!=0";
                		$result = $connection->fetchAll($sql);
                		if($result)
                		{
                        		$quantity_reduced_counter = 0;
                        		$net_qty = 0;
                                	foreach($result as $row)
                                	{
                                        	if($row['updated_qty'] == 0)continue;
                                        	$net_qty = (int)$row['updated_qty'];
                                        	if($net_qty>= $reducedQty)
                                        	{
                                                	$updateSql = "update ".$wms_inward_table." set updated_qty='".($net_qty-$reducedQty)."' where id=".$row['id'];
                                                	$connection->query($updateSql);
                                                	break;
                                        	}
                                        	else
                                        	{
                                                	if($quantity_reduced_counter == 0 && $net_qty != 0)
                                                	{
                                                        	$updateSql = "update ".$wms_inward_table." set updated_qty='".$quantity_reduced_counter."' where id=".$row['id'];
                                                        	$connection->query($updateSql);
                                                        	$reducedQty = $quantity_reduced_counter = ($reducedQty - $net_qty);

                                                	}
                                                	else
                                                	{
                                                        	if($net_qty>= $quantity_reduced_counter)
                                                        	{
                                                                	$updateSql = "update ".$wms_inward_table." set updated_qty='".($net_qty- $quantity_reduced_counter)."' where id=".$row['id'];
                                                                	$connection->query($updateSql);
                                                                	$quantity_reduced_counter = ($quantity_reduced_counter - $net_qty);
                                                                	break;
                                                        	}
                                                        	else{

                                                                	$updateSql = "update ".$wms_inward_table." set updated_qty='0' where id=".$row['id'];
                                                                	$connection->query($updateSql);
                                                                	$quantity_reduced_counter = ($quantity_reduced_counter - $net_qty);
                                                        	}
                                                	}
                                        	}
                                	}
                		}	
			}
			else{
				$UpdatedQty = ((int)$UpdatedQty - (int)$sourceInventory['quantity']);
                                $sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                $sourceItem             = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
                                $sourceItem->setSourceCode('CM-GGN');
                                $sourceItem->setSku($_REQUEST['sku']);
                                $sourceItem->setQuantity(0);
                                $sourceItem->setStatus(1);
                                $sourceItemInterface->execute([$sourceItem]);
				$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                $connection     = $resource->getConnection();
                                $tableName      = $resource->getTableName('wms_users'); //gives table name with prefix
                                $wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
                                $sql = "select * from ".$wms_inward_table." where sku='".$_REQUEST['sku']."' and warehouse='CM-GGN' and qty_addition!='' and qty_addition!=0 and updated_qty!='' and updated_qty!=0";
                                $result = $connection->fetchAll($sql);
                                if($result)
                                {
                                        $quantity_reduced_counter = 0;
                                        $net_qty = 0;
                                        foreach($result as $row)
                                        {
                                                if($row['updated_qty'] == 0)continue;
                                                $updateSql = "update ".$wms_inward_table." set updated_qty='0' where id=".$row['id'];
                                                $connection->query($updateSql);
                                        }
                                }
			}
		}
		else{
			if($sourceInventory['quantity']>= $UpdatedQty)
                        {
				$reduceQty  		= (int)$UpdatedQty;
                                $UpdatedQty 		= ((int)$sourceInventory['quantity'] - (int)$UpdatedQty);
                                $sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                $sourceItem             = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
                                $sourceItem->setSourceCode($sourceInventory['source_code']);
                                $sourceItem->setSku($_REQUEST['sku']);
                                $sourceItem->setQuantity($UpdatedQty);
                                $sourceItem->setStatus(1);
                                $sourceItemInterface->execute([$sourceItem]);
				$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                $connection     = $resource->getConnection();
                                $tableName      = $resource->getTableName('wms_users'); //gives table name with prefix
                                $wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
                                $sql = "select * from ".$wms_inward_table." where sku='".$_REQUEST['sku']."' and warehouse='".$sourceInventory['source_code']."' and qty_addition!='' and qty_addition!=0 and updated_qty!='' and updated_qty!=0";
                                $result = $connection->fetchAll($sql);
                                if($result)
                                {
                                        $quantity_reduced_counter = 0;
                                        $net_qty = 0;
                                        foreach($result as $row)
                                        {
                                                if($row['updated_qty'] == 0)continue;
                                                $net_qty = (int)$row['updated_qty'];
                                                if($net_qty>= $reducedQty)
                                                {
                                                        $updateSql = "update ".$wms_inward_table." set updated_qty='".($net_qty-$reducedQty)."' where id=".$row['id'];
                                                        $connection->query($updateSql);
                                                        break;
                                                }
                                                else
                                                {
                                                        if($quantity_reduced_counter == 0 && $net_qty != 0)
                                                        {
                                                                $updateSql = "update ".$wms_inward_table." set updated_qty='".$quantity_reduced_counter."' where id=".$row['id'];
                                                                $connection->query($updateSql);
                                                                $reducedQty = $quantity_reduced_counter = ($reducedQty - $net_qty);

                                                        }
                                                        else
                                                        {
                                                                if($net_qty>= $quantity_reduced_counter)
                                                                {
                                                                        $updateSql = "update ".$wms_inward_table." set updated_qty='".($net_qty- $quantity_reduced_counter)."' where id=".$row['id'];
                                                                        $connection->query($updateSql);
                                                                        $quantity_reduced_counter = ($quantity_reduced_counter - $net_qty);
                                                                        break;
                                                                }
                                                                else{

                                                                        $updateSql = "update ".$wms_inward_table." set updated_qty='0' where id=".$row['id'];
                                                                        $connection->query($updateSql);
                                                                        $quantity_reduced_counter = ($quantity_reduced_counter - $net_qty);
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                        else{
                                $UpdatedQty = ((int)$UpdatedQty - (int)$sourceInventory['quantity']);
                                $sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                $sourceItem             = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
                                $sourceItem->setSourceCode($sourceInventory['source_code']);
                                $sourceItem->setSku($_REQUEST['sku']);
                                $sourceItem->setQuantity(0);
                                $sourceItem->setStatus(1);
                                $sourceItemInterface->execute([$sourceItem]);
				$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                $connection     = $resource->getConnection();
                                $tableName      = $resource->getTableName('wms_users'); //gives table name with prefix
                                $wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
                                $sql = "select * from ".$wms_inward_table." where sku='".$_REQUEST['sku']."' and warehouse='".$sourceInventory['source_code']."' and qty_addition!='' and qty_addition!=0 and updated_qty!='' and updated_qty!=0";
                                $result = $connection->fetchAll($sql);
                                if($result)
                                {
                                        $quantity_reduced_counter = 0;
                                        $net_qty = 0;
                                        foreach($result as $row)
                                        {
                                                if($row['updated_qty'] == 0)continue;
                                                $updateSql = "update ".$wms_inward_table." set updated_qty='0' where id=".$row['id'];
                                                $connection->query($updateSql);
                                        }
                                }
                        }
		}
	}	
	/*$sourceItemInterface            = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
        $sourceItem                     = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
	$sourceItem->setSourceCode('CM-GGN');
        $sourceItem->setSku($_REQUEST['sku']);
        $sourceItem->setQuantity($_REQUEST['qty']);
        $sourceItem->setStatus(1);
        $sourceItemInterface->execute([$sourceItem]);
	$stockRegistry          = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $stockItem              = $stockRegistry->getStockItemBySku($_REQUEST['sku']);
        $stockItem->setQty($_REQUEST['qty']);
        $stockItem->setIsInStock((bool)$_REQUEST['qty']); // this line
        $stockRegistry->updateStockItemBySku($_REQUEST['sku'], $stockItem);
	*/
}

?>
