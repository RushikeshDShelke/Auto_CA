<?php
namespace Wms\CreditMemo\Observer;

class Inventoryrefilled implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {		
     //$order= $observer->getData('order');
	 //$order->doSomething();
	/* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
                $creditmemo = $observer->getEvent()->getCreditmemo();
                $order = $creditmemo->getOrder();
                $shipmentCollection = $order->getShipmentsCollection();
                foreach($shipmentCollection as $shipment){
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $shipmentCollections = $objectManager->create('Magento\Sales\Model\Order\Shipment');
			$shipmentobj = $shipmentCollections->load($shipment->getId());
                        foreach ($shipmentobj->getItemsCollection() as $item)
			{
                                $source_code_array[$item->getSku()]['source_code'] = $shipmentobj->getExtensionAttributes()->getSourceCode();
                                $source_code_array[$item->getSku()]['sku'] = $item->getSku();
				$source_code_array[$item->getSku()]['qty'] = $item->getQty();
                        }
		}
		foreach ($creditmemo->getAllItems() as $item) 
		{
                        $productId = $item->getProductId();
                        $productRepo = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
                	$product = $productRepo->getById($productId);
                	$productType = $product->getTypeId();
                	$qty = $item->getQty();

                	if (($item->getBackToStock() && $qty)) {
                        	if ($qty && $productType == 'simple') {
                                	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                                	$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                                	$connection = $resource->getConnection();
                                	//$connection  = $this->resourceConnection->getConnection();
                                	$wms_inward_table = $connection->getTableName('wms_inwards_data'); //gives table name with prefix
					$quantity_returned_in_wms = 0;
                                	//foreach($source_code_array as $ship_item)
					//{
					$sku = $item->getSku();
					//$shipment_sku = $source_code_array[$sku]['sku'];
					$warehouse = $source_code_array[$sku]['source_code'];
						//print_r($ship_item[$sku]['sku']); die;
                                        	//if($item->getSku() == $ship_item[$sku]['sku'] && $quantity_returned_in_wms == 0)
                                        	//{
                                                	$sql = "select id,updated_qty,sku from ".$wms_inward_table." where sku='".$item->getSku()."' and updated_qty!=0 and warehouse='". $warehouse."' limit 1";
							$result = $connection->fetchAll($sql);
							//print_r($result);
                                                	if(!$result)
                                                	{
                                                		$sql = "select id,updated_qty,sku from ".$wms_inward_table." where sku='".$item->getSku()."' and warehouse='". $warehouse."' and qty_addition!='' limit 1";
                                                		$result = $connection->fetchAll($sql);
							}
                                                	//$quantity_reduced_counter = 0;
							$net_qty = 0;
                                                	foreach($result as $row)
							{
								$resource1 = $objectManager->get('Magento\Framework\App\ResourceConnection');
								$connectionUpdate = $resource1->getConnection();
								$net_qty = (int)$row['updated_qty'];
								$quantity_to_be_updated = $net_qty+$qty;
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
                	}
                }
     return $this;
  }
}
