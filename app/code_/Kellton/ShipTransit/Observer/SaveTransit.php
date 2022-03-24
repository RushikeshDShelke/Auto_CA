<?php
namespace Kellton\ShipTransit\Observer;


use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

class SaveTransit implements \Magento\Framework\Event\ObserverInterface
{
     
     protected $_orderRepository;
	 protected $request;
	 protected $resourceConnection;
     
    public function __construct(
		\Magento\Catalog\Block\Product\Context $context,            
		OrderRepositoryInterface $_orderRepository,
		ShipmentRepositoryInterface $_shipmentRepository,
		RequestInterface $request,
		\Magento\Framework\App\ResourceConnection $resourceConnection  
		) {
			
		$this->_orderRepository 	= $_orderRepository;
		$this->_shipmentRepository	= $_shipmentRepository;	
		$this->request 			= $request;  
		$this->resourceConnection 	= $resourceConnection;     
    }


	public function execute(\Magento\Framework\Event\Observer $observer)
  	{
  		$connection  = $this->resourceConnection->getConnection();

        $data['comment'] = '';
        $data['status'] = 'intransit';
	$shipment  = $observer->getEvent()->getShipment();
	//print_r($shipment->getData()); die;
	$quantity_reduced_counter = 0;
	$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($shipment->getItemsCollection() as $item)
        {
	$sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
	$getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
        $AllInventoryBySources  = $getSourceItemsDataBySku->execute($item->getSku());
        $total_main_qty_CM = 0;
        foreach($AllInventoryBySources as $sourceInventory)
        {
                $total_main_qty_CM = (int)$total_main_qty_CM + (int)$sourceInventory['quantity'];
        }
	$StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
	$qty                    = $StockState->execute($item->getSku());
	$total_salable_qty      = $qty[0]['qty'];
	$baseUrlVar             = 'http://earth-fables.craftmaestros.com/';
	$baseUrlVar             = 'https://www.earthfables.com/';
	exec ("curl --data 'sku=".$item->getSku()."&salableqty=".(int)$total_salable_qty."&mainqty=".$total_main_qty_CM."&event=shipment_created&orderid=' ".$baseUrlVar."ProductQtySyncCraftSalable.php");
	$wms_inward_table = $this->resourceConnection->getTableName('wms_inwards_data'); //gives table name with prefix
	$sql = "select id,updated_qty,qty_addition,qc_reject,short_receipt,damage_warehouse from ".$wms_inward_table." where sku='".$item->getSku()."' and qty_addition!='' and warehouse='".$sourceCode."'";
	$result = $connection->fetchAll($sql);
	//print_r($result); die;
	
                    if($result)
                    {
				$quantity_reduced_counter = 0;
				$net_qty = 0;
				$orderedQty = $item->getQty();
                                foreach($result as $row)
				{
					if($row['updated_qty'] == 0)continue;
					$net_qty = (int)$row['updated_qty'];
					if($net_qty>= $orderedQty)
                                        {
                                                $updateSql = "update ".$wms_inward_table." set updated_qty='".($net_qty-$orderedQty)."' where id=".$row['id'];
                                                $connection->query($updateSql);
                                                break;
                                        }
                                        else
                                        {
                                                if($quantity_reduced_counter == 0 && $net_qty != 0)
                                                {
							$updateSql = "update ".$wms_inward_table." set updated_qty='".$quantity_reduced_counter."' where id=".$row['id'];
							$connection->query($updateSql);
							$orderedQty = $quantity_reduced_counter = ($orderedQty - $net_qty);
							
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
                                      //if($row['qty_addition']>=)
				}
			}
	}
		$orders    = $shipment->getOrder();
        $orderId   = $orders->getId();

	$order = $this->_orderRepository->get($orderId);
	foreach($order->getAllVisibleItems() as $item)
	{
		$partial_shipment = false;
		if($item->getQtyShipped() !=$item->getQtyOrdered())
		{
			$partial_shipment = true;
		}

	}
	if($partial_shipment)
	{
		$order->setState("complete")->setStatus("partial_intransit");
                $this->_orderRepository->save($order);
                $tableName = $connection->getTableName('sales_order');
                $sql = "UPDATE ".$tableName." SET state='complete',status = 'partial_intransit' WHERE entity_id='".$orderId."'";
                $this->resourceConnection->getConnection()->query($sql);
	}
	else{
        	$order->setState("complete")->setStatus("intrasit");
        	$this->_orderRepository->save($order);
		$tableName = $connection->getTableName('sales_order');
		$sql = "UPDATE ".$tableName." SET status = 'intransit' WHERE entity_id='".$orderId."'";
		$this->resourceConnection->getConnection()->query($sql);
	}
			
  	}

  
}
