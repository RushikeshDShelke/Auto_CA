<?php
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_WARNING & ~E_NOTICE); 
error_reporting(0);
use Magento\Framework\App\Bootstrap;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
require __DIR__ . '/../app/bootstrap.php';
$params 	= $_SERVER;
$bootstrap 	= Bootstrap::create(BP, $params);
$objectManager 	= $bootstrap->getObjectManager();
$state 		= $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager 	= \Magento\Framework\App\ObjectManager::getInstance();
$storeManager 	= $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl 	= $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
$resource 	= $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection 	= $resource->getConnection();
$tableName 	= $resource->getTableName('wms_users'); //gives table name with prefix
$wms_inward_table = $resource->getTableName('wms_inwards_data'); //gives table name with prefix
//echo "<pre>";print_r($_REQUEST); die;
$challan_no 		= $_REQUEST['challan_no'];
$challan_date 		= $_REQUEST['challan_date'];
$invoice_no 		= $_REQUEST['invoice_no'];
$invoice_date 		= $_REQUEST['invoice_date'];
$payment_term 		= $_REQUEST['payment_term'];
$payment_term_other 	= $_REQUEST['payment_term_other'];
$supplier_purchase_no 	= $_REQUEST['supplier_purchase_no'];
$sku 			= $_REQUEST['sku'];
$supplier_name 		= $_REQUEST['supplier_name'];
$total_salable_qty 	= $_REQUEST['total_salable_qty'];
$Debit_Note 		= $_REQUEST['Debit_Note'];
$qty_addition		= '';
if(isset($_REQUEST['qty_addition']))
$qty_addition 		= $_REQUEST['qty_addition'];
$warehouse 		= $_REQUEST['warehouse'];
$qc_reject           = '';
if(isset($_REQUEST['qc_reject']))
$qc_reject 		= $_REQUEST['qc_reject'];
$short_receipt           = '';
if(isset($_REQUEST['short_receipt']))
$short_receipt 		= $_REQUEST['short_receipt'];
$damages           = '';
if(isset($_REQUEST['damages']))
$damages 		= $_REQUEST['damages'];
$inv_price_without_gst 	= $_REQUEST['inv_price_without_gst'];
$gst 			= $_REQUEST['gst'];
//$short_receipt 		= $_REQUEST['short_receipt'];
$reduce_qty           = '';
if(isset($_REQUEST['reduce_qty']))
$reduce_qty 		= $_REQUEST['reduce_qty'];
$reduce_qty_radio           = '';
if(isset($_REQUEST['reduce_qty_radio']))
$reduce_qty_radio 	= $_REQUEST['reduce_qty_radio'];
$username		= $_SESSION['username'];

$insertData = [
	    'challan_number' =>$challan_no,
            'challan_date' => $challan_date,
            'invoice_number' => $invoice_no,
	    'invoice_date' => $invoice_date,
	    'payment_term' => $payment_term,
	    'payment_term_other' =>$payment_term_other,
	    'ref_purchase_no'=>$supplier_purchase_no,
	    'supplier_name'=> $supplier_name,
	    'sku' => $sku,
	    //'CMWH_existing'=> $CMWH_existing,
	    //'MTWH_existing'=> $MTWH_existing,
	    'total_salable_qty'=> $total_salable_qty,
	    'updated_qty' => ((int)$qty_addition - (int)$qc_reject - (int)$short_receipt - (int)$damages),
	    'warehouse'=> $warehouse,
	    'qty_addition'=>$qty_addition,
	    'qc_reject'=> $qc_reject,
	    'short_receipt'=>$short_receipt,
	    'damage_warehouse'=>$damages,
	    'reduce_qty'=>$reduce_qty,
	    'goods_return'=>$reduce_qty_radio,
	    'debit_note'=>$Debit_Note,
	    'invoice_price_without_gst'=> $inv_price_without_gst,
	    'gst_percentage'=> $gst,
	    'created_at'=> date("Y-m-d h:i:s"),
	    'created_by' => $username
    ];
	$connection->insert($wms_inward_table, $insertData);
//$short_receipt = $_REQUEST['short_receipt'];

if(isset($_REQUEST['sku']) && !empty($_REQUEST['sku']))
{
        $productRepository      	= $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $productObj             	= $productRepository->get($_REQUEST['sku']);
        $sourceItemInterface    	= $objectManager->get('\Magento\InventoryApi\Api\SourceItemsSaveInterface');
	$sourceItem 			= $objectManager->get('\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory')->create();
	$getSourceItemsDataBySku 	= $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
	$AllInventoryBySources 		= $getSourceItemsDataBySku->execute($_REQUEST['sku']);
	$netQtyToBeAdded 		= 0;
	$reduce_qty_requested		= 0;
	$orderedQty 			= 0;
	foreach($AllInventoryBySources as $sourceInventory)
	{
		if($sourceInventory['source_code'] == $_REQUEST['warehouse'])
		{
			if(isset($_REQUEST['qty_addition']) && $_REQUEST['qty_addition']!="")
				$_REQUEST['qty_addition'] = intval($_REQUEST['qty_addition']) + intval($sourceInventory['quantity']);
			if(isset($_REQUEST['reduce_qty']) && $_REQUEST['reduce_qty']!="")
			{
				$orderedQty             = intval($_REQUEST['reduce_qty']);
				$_REQUEST['reduce_qty']	= intval($sourceInventory['quantity']) - intval($_REQUEST['reduce_qty']);
			}
		}
	}

	if(isset($_REQUEST['qty_addition']) && $_REQUEST['qty_addition']!="")
	{
		$netQtyToBeAdded        = (intval($_REQUEST['qty_addition'])-intval($_REQUEST['qc_reject'])-intval($_REQUEST['short_receipt'])-intval($_REQUEST['damages']));
	}
	if(isset($orderedQty) && $orderedQty !=0)
	{
		$netQtyToBeAdded = intval($_REQUEST['reduce_qty']);
		$sql = "select * from ".$wms_inward_table." where sku='".$_REQUEST['sku']."' and warehouse='".$_REQUEST['warehouse']."' and qty_addition!='' and qty_addition!=0 and updated_qty!='' and updated_qty!=0";
		$result = $connection->fetchAll($sql);
		if($result)
		{
			$quantity_reduced_counter = 0;
			$net_qty = 0;
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
                                }		
		}

		
	}

        $sourceItem->setSourceCode($_REQUEST['warehouse']);
        $sourceItem->setSku($_REQUEST['sku']);
        $sourceItem->setQuantity($netQtyToBeAdded);
	$sourceItem->setStatus(1);
	$sourceItemInterface->execute([$sourceItem]);
	$StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        $qty                    = $StockState->execute($_REQUEST['sku']);
        $total_salable_qty      = $qty[0]['qty'];
	exec ("curl --data 'sku=".$_REQUEST['sku']."&qty=".(int)$total_salable_qty."' https://earthfables.craftmaestros.com/ProductQtySyncCraft.php");
	$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection     = $resource->getConnection();
        $sku_history = $resource->getTableName('sku_inventory_history'); //gives table name with prefix
        date_default_timezone_set('Asia/Kolkata');
        $insertData     = ["sku"=>$_REQUEST['sku'],
                           "reduced_qty"=> $reduce_qty,
                           "increased_qty"=> ((int)$qty_addition - (int)$qc_reject - (int)$short_receipt - (int)$damages),
                           "updated_salable_qty" => $total_salable_qty,
                           "action_comment"=>"WMS inventory Update.",
                           "created_at" =>date("Y-m-d h:i:s")
                          ];
        $result = $connection->insert($sku_history, $insertData);
        date_default_timezone_set('UTC');
	echo "Qty has been saved successfully....";
	//echo $total_salable_qty." ".$netQtyToBeAdded;
	echo "<a href='".$baseUrl."wms_inventory_inwards.php'>Continue Further</a>";
}

?>
