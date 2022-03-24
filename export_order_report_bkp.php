<?php


$host = "localhost"; // MySQL host name eg. localhost
$user = "root"; // MySQL user. eg. root ( if your on localserver)
$password = "crA7t5@dbma!"; // MySQL user password  (if password is not set for your root user then keep it empty )
$database = "Craftlive_new"; // MySQL Database name

// Connect to MySQL Database
$con = new mysqli($host, $user, $password, $database);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// get Users
$from_date = date('Y-m-d',strtotime($_POST['from']));
$to_date = date('Y-m-d',strtotime($_POST['to']));
$from_date = $from_date." 00:00:00";
$to_date = $to_date." 23:59:59";
    $query = "select ORD.increment_id as 'Order No.',ORD.created_at as 'Date', order_item.sku as 'SKU', order_item.name, order_item.qty_ordered,order_item.price_incl_tax as 'Price',order_item.row_total_incl_tax as 'MRP Total',(order_item.price * order_item.qty_ordered) as 'Base Total',order_item.tax_amount as 'Tax',ORD.base_shipping_amount as 'Shipping Charges',order_item.discount_amount,((order_item.price * order_item.qty_ordered)+(order_item.tax_amount) - order_item.discount_amount) as 'Revenue',ORD.customer_id as 'Customer_Id',ORD.customer_email as 'Email',ORD.status as 'Status',(SELECT v.value as 'Craftman Name' FROM  catalog_product_entity_int i LEFT JOIN eav_attribute_option o ON i.value = o.option_id LEFT JOIN eav_attribute_option_value v ON o.option_id = v.option_id AND v.store_id = 0 WHERE i.entity_id = order_item.product_id AND i.attribute_id = 155 AND i.store_id = 0), cata_prod.value as 'MC Price',if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=107,28,0))))) as 'MC GST %',((cata_prod.value*if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=107,28,0))))))/100) as 'MC GST Amount',(cata_prod.value+((cata_prod.value*if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=503 and entity_id=order_item.product_id)=107,28,0))))))/100)) as 'MC Price with GST' from sales_order_item as order_item LEFT JOIN sales_order as ORD ON ORD.entity_id = order_item.order_id LEFT JOIN catalog_product_entity_text AS cata_prod ON cata_prod.entity_id = order_item.product_id where cata_prod.attribute_id=179 AND ORD.created_at >= '".$from_date."'
AND ORD.created_at <= '".$to_date."'";
//$query = "SELECT customer_firstname,increment_id,created_at,base_grand_total,customer_email FROM sales_order";
if (!$result = mysqli_query($con, $query)) {
    exit(mysqli_error($con));
}

$users = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Order_report.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('Order No', 'Date', 'SKU', 'Product Name','qty_ordered', 'Price', 'MRP Total','Base Total','Tax','Shipping Charges','discount_amount','Revenue', 'Customer_Id', 'Email', 'Status', 'MC Name', 'MC Price without GST', 'MC GST %','MC GST Amount','MC Price with GST'));
if (count($users) > 0) {
    foreach ($users as $row) {
        fputcsv($output, $row);
    }
}
?>
