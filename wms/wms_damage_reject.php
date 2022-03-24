<!DOCTYPE html>
<html>
<body>

<h1>Select Dates</h1>

<form method='POST' action='wms_damage_reject_report.php'>
  <label for="birthday">FROM:</label>
  <input type="date" id="from" name="from">
  <label for="birthday">TO:</label>
  <input type="date" id="to" name="to">
  <input type="submit">
</form>


</body>
</html>

<?php
/*if($_POST)
{
$from_date = date('Y-m-d',strtotime($_POST['from']));
$to_date = date('Y-m-d',strtotime($_POST['to']));
        $sql = "select ORD.increment_id as 'Order No.',ORD.created_at as 'Date', order_item.sku as 'SKU', order_item.qty_ordered,order_item.price_incl_tax as 'Price',order_item.row_total_incl_tax as 'MRP Total',(order_item.price * order_item.qty_ordered) as 'Base Total',order_item.tax_amount as 'Tax',order_item.discount_amount,((order_item.price * order_item.qty_ordered)+(order_item.tax_amount) - order_item.discount_amount) as 'Revenue',ORD.customer_id as 'Customer_Id',ORD.customer_email as 'Email',ORD.status as 'Status', cata_prod.value as 'MC Price' from sales_order_item as order_item LEFT JOIN sales_order as ORD ON ORD.entity_id = order_item.order_id LEFT JOIN catalog_product_entity_text AS cata_prod ON cata_prod.entity_id = order_item.product_id where cata_prod.attribute_id=179 AND ORD.created_at >= '".$from_date."'
AND ORD.created_at >= '".$to_date."'";
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'crA7t5@dbma!';
$db = "Craftlive_new";
$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
//get records from database
$query = $conn->query($sql);
if($query->num_rows > 0){
    $delimiter = ",";
    $filename = "order_report" . date('Y-m-d') . ".csv";

    //create a file pointer
    $f = fopen('php://memory', 'w');

    //set column headers
    $fields = array('Order No.', 'Date', 'SKU', 'Price', 'MRP Total', 'Base Total');
    fputcsv($f, $fields, $delimiter);

    //output each row of the data, format line as csv and write to file pointer
    while($row = $query->fetch_assoc()){
        //print_r($row);
        //$status = ($row['status'] == '1')?'Active':'Inactive';
        $lineData = array($row['Order No'], $row['Date'], $row['SKU'], $row['qty_ordered'], $row['Price'], $row['MRP Total'],$row['Base Total'],$row['Tax'],$row['discount_amount'],$row['Revenue'], $row['Customer_Id'], $row['Email'], $row['Status'], $row['MC Price']);
        fputcsv($f, $lineData, $delimiter);
    }

    //move back to beginning of file
    fseek($f, 0);

    //set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    //output all remaining data on a file pointer
    fpassthru($f);
}
$conn -> close();
exit;
}
print_r($_POST);*/
?>

