<?php 


$host = "localhost"; // MySQL host name eg. localhost
$user = "cmdbuser"; // MySQL user. eg. root ( if your on localserver)
$password = "Cr@ft123$"; // MySQL user password  (if password is not set for your root user then keep it empty )
$database = "Craftlive_new"; // MySQL Database name

// Connect to MySQL Database
$con = new mysqli($host, $user, $password, $database);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// get Users
$query = "SELECT customer_firstname,increment_id,created_at,base_grand_total,customer_email FROM sales_order";
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
fputcsv($output, array('Customer Name', 'Order No', 'Order Date', 'Order Value', 'Email ID'));
if (count($users) > 0) {
    foreach ($users as $row) {
        fputcsv($output, $row);
    }
}
?>
