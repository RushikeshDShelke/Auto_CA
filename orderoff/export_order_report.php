<?php


$host = "localhost"; // MySQL host name eg. localhost
$user = "cmdbuser"; // MySQL user. eg. root ( if your on localserver)
$password = "5Rr#u@PGm6FKHg@3L4cA"; // MySQL user password  (if password is not set for your root user then keep it empty )
$database = "craft_live"; // MySQL Database name

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
$query = "select ORD.increment_id as 'Order No.',ORD.created_at as 'Date', if(sop.method='checkmo','COD',sop.method),order_item.sku as 'SKU', order_item.name as 'Name',order_item.qty_ordered,order_item.qty_refunded,if(order_item.qty_refunded > 0,'Refunded','Ordered'),order_item.price_incl_tax as 'Price',order_item.row_total_incl_tax as 'MRP Total',(order_item.price * order_item.qty_ordered) as 'Base Total',order_item.tax_amount as 'Tax',ORD.base_shipping_amount as 'Shipping Charges',order_item.discount_amount,((order_item.price * order_item.qty_ordered)+(order_item.tax_amount) - order_item.discount_amount) as 'Revenue',ORD.customer_id as 'Customer_Id',ORD.customer_email as 'Email',ORD.status as 'Status',(select option_value.value from eav_attribute_option_value as option_value where option_value.option_id=(select entity_int.value from catalog_product_entity_int as entity_int where entity_int.attribute_id=148 and entity_int.entity_id=order_item.product_id limit 1)), (select cata_prod.value from catalog_product_entity_text AS cata_prod where cata_prod.entity_id = order_item.product_id AND cata_prod.attribute_id=179) as 'MC Price',if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=107,28,0))))) as 'MC GST %',((cata_prod.value*if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=107,28,0))))))/100) as 'MC GST Amount',(cata_prod.value+((cata_prod.value*if((select value from
catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=107,28,0))))))/100)) as 'MC Unit Price with GST', ((cata_prod.value+((cata_prod.value*if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=103,3,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=104,5,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=105,12,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=106,18,if((select value from catalog_product_entity_int where attribute_id=501 and entity_id=order_item.product_id)=107,28,0))))))/100))*order_item.qty_ordered) as 'MC Total Price with GST' from sales_order_item as order_item LEFT JOIN sales_order as ORD ON ORD.entity_id = order_item.order_id LEFT JOIN catalog_product_entity_text AS cata_prod ON cata_prod.entity_id = order_item.product_id LEFT JOIN sales_order_payment as sop ON ORD.entity_id = sop.parent_id where cata_prod.attribute_id=179 AND ORD.created_at >= '".$from_date."'
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
// echo "<pre>";
// print_r($users);
// exit();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Order_report.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('Order No', 'Date', 'Payment Mode', 'SKU', 'Name', 'qty_ordered', 'No of Items refunded', 'Item Status', 'Price', 'MRP Total','Base Total','Tax','Shipping Charges','discount_amount','Revenue', 'Customer_Id', 'Email', 'Status', 'MC Name', 'MC Price without GST', 'MC GST %','MC GST Amount','MC Unit Price with GST','MC Total Price with GST'));
if (count($users) > 0) {
    foreach ($users as $row) {
        fputcsv($output, $row);
    }
}
?>

