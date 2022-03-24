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
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

if(isset($_REQUEST['sku_dropdown']) && !empty($_REQUEST['sku_dropdown']))
{
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sql = "select soi.sku,soi.qty_ordered,so.increment_id,so.state,so.status from sales_order as so LEFT JOIN sales_order_item as soi ON so.entity_id=soi.order_id where soi.sku='".$_REQUEST['sku_dropdown']."' and so.state IN('pending','processing','pending_payment','new') group by so.entity_id";
        $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
        if($result)
        { ?>
                <table>
                        <th>SKU</th>
                        <th>Qty Ordered</th>
                        <th>Order ID</th>
                        <th>State</th>
                        <th>Status</th>

<?php
                foreach($result as $row){
                        echo "<tr>";
                        echo "<td>".$row['sku']."</td>";
                        echo "<td>".$row['qty_ordered']."</td>";
                        echo "<td>".$row['increment_id']."</td>";
                        echo "<td>".$row['state']."</td>";
                        echo "<td>".$row['status']."</td>";
                        echo "</tr>";
                }
                echo "</table>";
        }
        else{die("No Record Found");}
}
else
{
        if(isset($_REQUEST['name_dropdown']) && !empty($_REQUEST['name_dropdown']))
        {
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sql = "select soi.sku,soi.qty_ordered,so.increment_id,so.state,so.status from sales_order as so LEFT JOIN sales_order_item as soi ON so.entity_id=soi.order_id where soi.sku='".$_REQUEST['sku_dropdown']."' and so.state IN('pending','processing','pending_payment','new') group by so.entity_id";
        $result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
        if($result)
        { ?>
                <table>
                        <th>SKU</th>
                        <th>Qty Ordered</th>
                        <th>Order ID</th>
                        <th>State</th>
                        <th>Status</th>

<?php
                foreach($result as $row){
                        echo "<tr>";
                        echo "<td>".$row['sku']."</td>";
                        echo "<td>".$row['qty_ordered']."</td>";
                        echo "<td>".$row['increment_id']."</td>";
                        echo "<td>".$row['state']."</td>";
                        echo "<td>".$row['status']."</td>";
                        echo "</tr>";
                }
                echo "</table>";
        }
        else{die("No Record found");}
        }
}
