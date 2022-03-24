<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

if (isset($_REQUEST['sku']) && isset($_REQUEST['salableqty']) && isset($_REQUEST['mainqty'])) {
        $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection     = $resource->getConnection();
        $tableName      = $resource->getTableName('inventory_reservation'); //gives table name with prefix
        $StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $qty                    = $StockState->execute($_REQUEST['sku']);
        $total_salable_qty_CM   = $qty[0]['qty'];
        $toal_salable_qty_EF    = $_REQUEST['salableqty'];
        $UpdatedQty = $QtyToBeSynced = ((int)$total_salable_qty_CM - (int)$toal_salable_qty_EF);
        $getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
	$AllInventoryBySources  = $getSourceItemsDataBySku->execute($_REQUEST['sku']);
	$sourceCounter=0;
	foreach($AllInventoryBySources as $wh)
	{
        	if($sourceCounter==0)
        	{
                	if($wh['source_code'] != 'CM-GGN')
                	{
                        	$AllInventoryBySources = array_reverse($AllInventoryBySources);
                	}
        	}
        	$sourceCounter++;
	}
	$total_main_qty_CM = 0;
	$CM_GGN_Inventory = 0;
        foreach($AllInventoryBySources as $sourceInventory)
        {
		$total_main_qty_CM = (int)$total_main_qty_CM + (int)$sourceInventory['quantity'];
		if($sourceInventory['source_code'] == 'CM-GGN')
		$CM_GGN_Inventory = (int)$CM_GGN_Inventory + (int)$sourceInventory['quantity'];
        }

        $UpdatedQtyMain = $QtyToBeSyncedMain = ((int)$total_main_qty_CM - (int)$_REQUEST['mainqty']);
        if($_REQUEST['event'] == 'order_placed')
        {
                $metadata = '{"event_type":"'.$_REQUEST['event'].'","object_type":"order","object_id":"EF-'.$_REQUEST['orderid'].'"}';
                $QtyToBeSynced = -abs($QtyToBeSynced);
        }
        if($_REQUEST['event'] == 'order_canceled')
        {
                $metadata = '{"event_type":"'.$_REQUEST['event'].'","object_type":"order","object_id":"EF-'.$_REQUEST['orderid'].'"}';
                $QtyToBeSynced = abs($QtyToBeSynced);
        }
        if($_REQUEST['event'] == 'refunded')
        {
                $metadata = '{"event_type":"'.$_REQUEST['event'].'","object_type":"order","object_id":"EF-'.$_REQUEST['orderid'].'"}';
		$QtyToBeSynced = abs($QtyToBeSyncedMain);
		$QtyToBeSynced = $QtyToBeSynced + $CM_GGN_Inventory;
                //$QtyToBeSynced = $total_main_qty_CM + $QtyToBeSynced;

                $sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                $sourceItem             = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
                                $sourceItem->setSourceCode('CM-GGN');
                                $sourceItem->setSku($_REQUEST['sku']);
                                $sourceItem->setQuantity($QtyToBeSynced);
                                $sourceItem->setStatus(1);
                                $sourceItemInterface->execute([$sourceItem]);
                 $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                        $connection = $resource->getConnection();
                                        //$connection  = $this->resourceConnection->getConnection();
                                        $wms_inward_table = $connection->getTableName('wms_inwards_data'); //gives table name with prefix
                                        $quantity_returned_in_wms = 0;
                                        //foreach($source_code_array as $ship_item)
                                        //{
                                        $sku = $_REQUEST['sku'];
                                        //$shipment_sku = $source_code_array[$sku]['sku'];
                                        $warehouse = 'CM-GGN';
                                                //print_r($ship_item[$sku]['sku']); die;
                                                //if($item->getSku() == $ship_item[$sku]['sku'] && $quantity_returned_in_wms == 0)
                                                //{
                                                        $sql = "select id,updated_qty,sku from ".$wms_inward_table." where sku='".$sku."' and updated_qty!=0 and warehouse='". $warehouse."' limit 1";
                                                        $result = $connection->fetchAll($sql);
                                                        //print_r($result);
                                                        if(!$result)
                                                        {
                                                                $sql = "select id,updated_qty,sku from ".$wms_inward_table." where sku='".$sku."' and warehouse='". $warehouse."' and qty_addition!='' limit 1";
                                                                $result = $connection->fetchAll($sql);
                                                        }
                                                        //$quantity_reduced_counter = 0;
                                                        $net_qty = 0;
                                                        foreach($result as $row)
                                                        {
                                                                $resource1 = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                                                $connectionUpdate = $resource1->getConnection();
                                                                $net_qty = (int)$row['updated_qty'];
                                                                $quantity_to_be_updated = $QtyToBeSynced;
                                                                $data = ["updated_qty"=>$quantity_to_be_updated]; // Key_Value Pair
                                                                $where = ['id = ?' => (int)$row['id']];
                                                                $updateResult = $connectionUpdate->update($wms_inward_table, $data, $where);
                                                                //echo $updateSql = "update ".$wms_inward_table." set updated_qty='".($net_qty+$qty)."' where id=".$row['id']; die;
                                                                //$updateResult = $connectionUpdate->query($updateSql);

                                                                //$quantity_returned_in_wms = $qty;
                                                        }
                                                //}
                                        //}




        }
        if($_REQUEST['event'] == 'shipment_created')
        {
                $metadata = '{"event_type":"'.$_REQUEST['event'].'","object_type":"order","object_id":"EF-'.$_REQUEST['orderid'].'"}';
                $UpdatedQty = $QtyToBeSynced = abs($QtyToBeSyncedMain);
                $getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
		$AllInventoryBySources  = $getSourceItemsDataBySku->execute($_REQUEST['sku']);
		$sourceCounter=0;
        	foreach($AllInventoryBySources as $wh)
        	{
                	if($sourceCounter==0)
                	{
                        	if($wh['source_code'] != 'CM-GGN')
                        	{
                                	$AllInventoryBySources = array_reverse($AllInventoryBySources);
                        	}
                	}
                	$sourceCounter++;
        	}
        foreach($AllInventoryBySources as $sourceInventory)
        {
                if($sourceInventory['source_code'] == 'CM-GGN')
                {
                        if($sourceInventory['quantity']>= $UpdatedQty)
                        {
                                $netQtyToBeAdded = $reducedQty = (int)$UpdatedQty;
                                $UpdatedQty = ((int)$sourceInventory['quantity'] - (int)$UpdatedQty);
                                $sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                $sourceItem             = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
                                $sourceItem->setSourceCode('CM-GGN');
                                $sourceItem->setSku($_REQUEST['sku']);
                                $sourceItem->setQuantity($UpdatedQty);
                                $sourceItem->setStatus(1);
                                $sourceItemInterface->execute([$sourceItem]);
                                //$netQtyToBeAdded = intval($_REQUEST['reduce_qty']);
                                $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                $connection     = $resource->getConnection();
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
				break;
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
                                $reduceQty              = (int)$UpdatedQty;
                                $UpdatedQty             = ((int)$sourceInventory['quantity'] - (int)$UpdatedQty);
                                $sourceItemInterface    = $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
                                $sourceItem             = $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
                                $sourceItem->setSourceCode($sourceInventory['source_code']);
                                $sourceItem->setSku($_REQUEST['sku']);
                                $sourceItem->setQuantity($UpdatedQty);
                                $sourceItem->setStatus(1);
                                $sourceItemInterface->execute([$sourceItem]);
                                $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                $connection     = $resource->getConnection();
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
				break;
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

        }
        $data = [
          "stock_id" => 2,
          "sku" => $_REQUEST['sku'],
          "quantity" => $QtyToBeSynced,
          "metadata" => $metadata
      ];
        if($_REQUEST['event'] != 'refunded')
        {
                $connection->insert($tableName, $data);
        }
}
?>

