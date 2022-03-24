<style>
      table,
      th,
      td {
        padding: 10px;
        border: 1px solid black;
        border-collapse: collapse;
      }
    </style>
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
if(isset($_SESSION['id']) && !empty($_SESSION['id']) && isset($_SESSION['username']) && !empty($_SESSION['username']) && isset($_SESSION['role']) && !empty($_SESSION['role']))
{
        echo "<div class='top-container' style='background: #C3D9FF;
                                padding: 5px 10px 10px 5px;
                                margin-top: -8px;
                                margin-left: -8px;
                                margin-right: -8px;'>
        <div class='welcome' style='float:left;'><span>Welcome, ".$_SESSION['username']." ||</span></div>";
                                echo "<div class='logout'>";
                                if($_SESSION['role'] != 'VIEWER')
                                {
                                        echo "<a href='".$baseUrl."wms_inventory_inwards.php'>Inward form || </a>";
                                }
                                echo "<a href='".$baseUrl."wms/wms_inventory_report.php'>Inventory Report || </a>";
                                if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'ALL-WH'){echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>"; }
                                //echo "<a href='".$baseUrl."wms_PO_inwards.php'>PO Generation || </a>";
                                echo "<a href='".$baseUrl."wms/wms_PO_listing.php'>Purchase Orders || </a>";
                                echo "<a href='".$baseUrl."wms_logout.php'>Logout</a></div>";
                                echo "<!--<div class='logout'> <a href='".$baseUrl."wms_logout.php'>Logout</a></div>--></div>";

}
else{
        echo "<a href='".$baseUrl."wms_login.php'>Click here</a> to Login. ";
        die("You're not authorised to see this page.");
}
$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection     = $resource->getConnection();
$sku = '';
if(isset($_REQUEST['sku_textbox']) && !empty($_REQUEST['sku_textbox']) || isset($_REQUEST['sku_dropdown']) && !empty($_REQUEST['sku_dropdown']))
{
        if(isset($_REQUEST['sku_textbox']) && !empty($_REQUEST['sku_textbox']))
        $sku                    = $_REQUEST['sku_textbox'];
        else
        $sku                    = $_REQUEST['sku_dropdown'];
}
else{
        if(isset($_REQUEST['name_dropdown']) && !empty($_REQUEST['name_dropdown']))
        {
                $sku                    = $_REQUEST['name_dropdown'];
        }
}
?>
<?php
if($_POST)
{
$filterVariables = '';
$challan_no = 0;
$invoice_no = 0;
$warehouse = 0;
//$sku = 0;
$supplier_list = 0;
if(isset($_POST['challan_no']) && !empty($_POST['challan_no']))
{
         $challan_no = $_POST['challan_no'];
         $filterVariables .= " and challan_number='".$_POST['challan_no']."'";
}
if(isset($_POST['invoice_no']) && !empty($_POST['invoice_no']))
{
         $invoice_no = $_POST['invoice_no'];
         $filterVariables .= " and invoice_number='".$_POST['invoice_no']."'";
}
if(isset($_POST['warehouse_list']) && !empty($_POST['warehouse_list']))
{
        $warehouse = $_POST['warehouse_list'];
         $filterVariables .= " and warehouse='".$_POST['warehouse_list']."'";
}
if(isset($_POST['sku_textbox']) && !empty($_POST['sku_textbox']))
{
        $sku = $_POST['sku_textbox'];
         $filterVariables .= " and sku='".$_POST['sku_textbox']."'";
}
else{
        if(isset($_POST['sku_list']) && !empty($_POST['sku_list']))
        {
                $sku = $_POST['sku_list'];
                 $filterVariables .= " and sku='".$_POST['sku_list']."'";
        }
        else{
                if(isset($_POST['name_list']) && !empty($_POST['name_list']))
                {
                        $sku = $_POST['name_list'];
                        $filterVariables .= " and sku='".$_POST['name_list']."'";
                }
        }
}
if(isset($_POST['supplier_list']) && !empty($_POST['supplier_list']))
{
        $supplier_list = $_POST['supplier_list'];
         $filterVariables .= " and supplier_name='".$_POST['supplier_list']."'";
}
//echo $filterVariables; die;
//echo "<pre>";print_r($_POST); die;
//Select Data from tablei
$sku_history = 'sku_inventory_history';
$sql = "Select id,sku,reduced_qty,increased_qty,updated_salable_qty,action_comment,created_at FROM " .$sku_history. " where sku = '".$sku."'".$filterVariables;
$result = $connection->fetchAll($sql);
//print_r($result); die;
//echo $invoice_no." -- ".$challan_no." -- ".$warehouse." -- ".$sku." -- ".$supplier_list." -- "; die;
?>

<form action="csv_download.php" method="post">
<input type="hidden" name="csv_invoice_no" value="<?php echo trim($invoice_no) ?>" />
<input type="hidden" name="csv_challan_no" value="<?php echo trim($challan_no) ?>" />
<input type="hidden" name="csv_warehouse" value="<?php echo trim($warehouse) ?>" />
<input type="hidden" name="csv_sku" value="<?php echo trim($sku) ?>" />
<input type="hidden" name="csv_supplier_name" value="<?php echo trim($supplier_list) ?>" />
<input type="submit" name="csv_download" value="Download CSV" />
</form>
<?php
//array_to_csv_download($result,"WMS_Inventory".date().".csv",);
if($result)
{
        echo "<table><th>SKU</th><th>Reduced Qty</th><th>Increased Qty</th><th>Updated Salable Qty</th><th>Action Comment</th><th>Created At</th>";
        //if($_SESSION['role'] != 'VIEWER')echo "<th>Action</th>";
        $open_order_total = 0;
        $main_qty = 0;
        foreach($result as $item)
        {
        /*      $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
                $productObj = $productRepository->get($item['sku']);
                //echo $productObj->getMarketplaceFee(); die;
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
                //$mc_price_gst_value   = ($productObj->getMarketplaceFee() * $item['gst_percentage'])/100;
                //$mc_price_gst_value   = $item['inv_price_without_gst']
                $mc_price_without_gst   = (int)$item['invoice_price_without_gst']; //Invoice Unit Price without GST
                $gst_value              = ((int)$item['invoice_price_without_gst']*(int)$item['gst_percentage'])/100; //Invoice GST value of Unit price
                $mc_price_with_gst      = ($mc_price_without_gst + $gst_value); //Invoice Unit Price with GST
                $total_mc_value_gst     = ($mc_price_with_gst * $item['updated_qty']); //Total MC price with GST
                //$total_mc_value       = (int)$item['invoice_price_without_gst'] + $total_mc_value_gst;
                if($sku)
                {
                        $main_qty = (int)$main_qty + (int)$item['updated_qty'];
                        $open_order_total = $open_orders_qty;
                }
         */
                /* echo "<tr><td>".$item['warehouse']."</td><td>".$item['supplier_name']."</td><td>".$item['sku']."</td><td>".$productObj->getName()."</td><td>".$item['challan_number']."</td><td>".$item['invoice_number']."</td><td>".$warehouseInventory."</td><td>".$item['challan_date']."</td><td>".$item['invoice_date']."</td><td>".$no_of_days."</td><td>".$productObj->getMarketplaceFee()."</td><td>".$mc_price_with_gst."</td><td>". $total_mc_value."</td></tr>"; */
                echo "<tr><td>".$item['sku']."</td><td>".$item['reduced_qty']."</td><td>".$item['increased_qty']."</td><td>".$item['updated_salable_qty']."</td><td>".$item['action_comment']."</td><td>".$item['created_at']."</td>";
                //if($_SESSION['role'] != 'VIEWER')
                        //echo "<td><a href='".$baseUrl."wms/wms_inwards_edit.php?id=".$item['id']."'>Edit</a></td>";
                echo "</tr>";
        }
        echo "</table>";
}}
function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w');
    // loop over the input array
    foreach ($array as $line) {
        // generate csv lines from the inner arrays
        fputcsv($f, $line, $delimiter);
    }
    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}
?>
