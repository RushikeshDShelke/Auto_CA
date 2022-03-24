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
<td>'.$row['increment_id'].'</td>
            <td>'.$row['created_at'].'</td>
            <td>'.$row['base_grand_total'].'</td>
            <td>'.($row['base_grand_total']-$row['tax_amount']).'</td>
            <td>'.$row['base_shipping_amount'].'</td>

// get Users
$query = "SELECT increment_id,created_at,base_grand_total,(base_grand_total-tax_amount) as 'Order Base Value',base_shipping_amount FROM sales_order";
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
header('Content-Disposition: attachment; filename=Users.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array('Order No', 'Order Date', 'Order Value', 'Order Base Value', 'Shipping_value'));
if (count($users) > 0) {
    foreach ($users as $row) {
        fputcsv($output, $row);
    }
}
?>
