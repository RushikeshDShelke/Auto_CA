<?php 
session_start();

use Magento\Framework\App\Bootstrap;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
require __DIR__ . '/../app/bootstrap.php';
$params         = $_SERVER;
$bootstrap      = Bootstrap::create(BP, $params);
$objectManager  = $bootstrap->getObjectManager();
$state          = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager   = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl        = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$wms_inward_table= $resource->getTableName('wms_inwards_data'); //gives table name with prefix
if($_POST)
{
$filterVariables = 0;
$challan_no = 0;
$invoice_no = 0;
$warehouse = 0;
$sku = 0;
$supplier_list = 0;
if(isset($_POST['csv_challan_no']) && !empty($_POST['csv_challan_no']))
{
         $filterVariables .= " and challan_number='".$_POST['csv_challan_no']."'";
}
if(isset($_POST['csv_invoice_no']) && !empty($_POST['csv_invoice_no']))
{
         $filterVariables .= " and invoice_number='".$_POST['csv_invoice_no']."'";
}
if(isset($_POST['csv_warehouse']) && !empty($_POST['csv_warehouse']))
{
         $filterVariables .= " and warehouse='".$_POST['csv_warehouse']."'";
}
if(isset($_POST['csv_sku']) && !empty($_POST['csv_sku']))
{
        $filterVariables .= " and sku='".$_POST['csv_sku']."'";
}
if(isset($_POST['csv_supplier_name']) && !empty($_POST['csv_supplier_name']))
{
         $filterVariables .= " and supplier_name='".$_POST['csv_supplier_name']."'";
}
$sql = "Select created_at,warehouse,supplier_name,sku,challan_number,challan_date,invoice_number,invoice_date,updated_qty,invoice_price_without_gst,gst_percentage FROM " . $wms_inward_table. " where qty_addition!='' and qty_addition!=0 and updated_qty!='' and updated_qty!=0".$filterVariables;
$result = $connection->fetchAll($sql);
$output = fopen("php://output",'w') or die("Can't open php://output");
header("Content-Type:application/csv"); 
header("Content-Disposition:attachment;filename=WMS_Inventory_Report.csv"); 
fputcsv($output, array('Entry Date','warehouse','Supplier Name','sku','Product Name','Challan No','Invoice No','Main Qty','Open Orders','challan Date','Invoice Date','Ageing','MC Price/unit without GST','MC Price/Unit with GST','Total MC Value'));
$counter =0;
$final_formatted_array = array();
foreach($result as $item) {
	$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                $productObj = $productRepository->get($item['sku']);
                $getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
                $AllInventoryBySources  = $getSourceItemsDataBySku->execute($item['sku']);
                $Ageing_date = 0;
                if($item['invoice_date'] != '' && $item['challan_date']!= '')
                {
                        if($item['invoice_date'] < $item['challan_date'])
                        {
                                $Ageing_date = $item['invoice_date'];
                        }
                        else{
                                $Ageing_date = $item['challan_date'];
                        }
                }
                if($item['invoice_date'] != '' && $item['challan_date']== '')
                {
                        $Ageing_date = $item['invoice_date'];
                }
                if($item['invoice_date'] == '' && $item['challan_date'] != '')
                {
                        $Ageing_date = $item['challan_date'];
                }
                if($item['invoice_date'] == '' && $item['challan_date'] == '')
                {
                        $Ageing_date = $item['created_at'];
                }
                $date1_ts       = strtotime($Ageing_date);
                $date2_ts       = strtotime(date("Y-m-d"));
                $diff           = $date2_ts - $date1_ts;
                $no_of_days     = round($diff / 86400);
		$item['ageing'] = $no_of_days;
		$item['name'] = $productObj->getName();
		$warehouseInventory = 0;
                $total_all_warehouse_main_qty = 0;
                foreach($AllInventoryBySources as $sourceInventory)
                {
                        $total_all_warehouse_main_qty = $total_all_warehouse_main_qty + $sourceInventory['quantity'];
                        if($sourceInventory['source_code']==$item['warehouse'])
                        {
                                $warehouseInventory = $sourceInventory['quantity'];
                        }
                }
                $StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
                $qty                    = $StockState->execute($item['sku']);
                $salable_qty            = $qty[0]['qty'];
                $open_orders_qty        = (int)$total_all_warehouse_main_qty - (int)$salable_qty;
                $mc_price_without_gst   = (int)$item['invoice_price_without_gst']; //Invoice Unit Price without GST
                $gst_value              = ((int)$item['invoice_price_without_gst']*(int)$item['gst_percentage'])/100; //Invoice GST value of Unit price
                $mc_price_with_gst      = ($mc_price_without_gst + $gst_value); //Invoice Unit Price with GST
                $total_mc_value_gst     = ($mc_price_with_gst * $item['updated_qty']); //Total MC price with GST
			$final_formatted_array = array("created_at"=>$item['created_at'],
								"warehouse" => $item['warehouse'],
								"supplier_name" => $item['supplier_name'],
								"sku" => $item['sku'],
								"name" => $productObj->getName(),
								"challan_number" => $item['challan_number'],
								"invoice_number" => $item['invoice_number'],
								"updated_qty" => $item['updated_qty'],
								"open_order" => $open_orders_qty,
								"challan_date" => $item['challan_date'],
                                                                "invoice_date" => $item['invoice_date'],
                                                                "ageing" => $no_of_days,
                                                                "mc_price_without_gst" => $mc_price_without_gst,
                                                                "mc_price_with_gst" => $mc_price_with_gst,
								"total_mc_value_gst" => $total_mc_value_gst
								);
    fputcsv($output, $final_formatted_array);
}
fclose($output) or die("Can't close php://output");
}
?>
