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
error_reporting(0);

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
if(isset($_REQUEST['id']) && $_REQUEST['id'])
{
	$resource               = $objectManager->get('Magento\Framework\App\ResourceConnection');
	$connection             = $resource->getConnection();
	$delivery_challan_data  = $resource->getTableName('delivery_challan_data'); //gives table name with prefix
	$sql                    = "select * from ".$delivery_challan_data." where id=".$_REQUEST['id'];
	$result 		= $connection->fetchAll($sql);
	if($result)
	{ 
		$sqlJoin = "select dcd.id,dcd.challan_date,dcd.order_id,dci.name,dci.price,dci.quantity,dcad.address_data from delivery_challan_data as dcd LEFT JOIN delivery_challan_items as dci ON dcd.id=dci.challan_id LEFT JOIN delivery_challan_addresses_data as dcad ON dcd.address_id=dcad.address_id where dcd.id=".$result[0]['id'];
		$resultJoin                 = $connection->fetchAll($sqlJoin);
		if($resultJoin)
		{
	?>
	
	<table>
		<tr><th colspan="5"><h1>Delivery Challan</h1></th></tr>
		<tr><th colspan="5"><h3>Delivery Note</h3></th></tr>
		<tr>
			<td rowspan="3" colspan="3" style="max-width:220px;">Ammara Craft Maestros Pvt .Ltd, C-1524 Basement Block C Sushant Lock Phase 1 Haryana 122101</td>
			<td>Delivery Note No.<br> <?php echo "CMCH202100".$result[0]['id'] ?> </td>
			<td>Dated. <br> <?php echo $result[0]['challan_date'] ?></td>
		</tr>
		<tr>
		<td></td>
		<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
		<td rowspan="6" colspan="3"><?php echo $resultJoin[0]['address_data']?></td>
                        <td>Buyer's Order No:</td>
			<td><?php if($result[0]['order_id']){echo $result[0]['order_id'];} else{ echo "N/A";}?></td>
		</tr>
		<tr>
                        <td></td>
                        <td></td>
                </tr>
		<tr>
                        <td></td>
                        <td></td>
                </tr>
		<tr>
			<td rowspan="3">Terms of Delivery</td>
			<td><?php if($result[0]['returnable']){echo $result[0]['returnable'];}else{echo "No";}?></td>
		</tr>
		<tr>
		</tr>
		<tr>
		</tr>
		<tr>
		</tr>
		<tr>
			<td>Description of Goods</td>
                        <td>Quantity</td>
                        <td>Rate</td>
			<td>Per</td>
			<td>Amount</td>
		</tr>
		<?php 
			$subTotal = 0;
			foreach($resultJoin as $rowJoin)
			{
				$subTotal = $subTotal + ($rowJoin['price']*$rowJoin['quantity']);
				echo "<tr><td>".$rowJoin['name']."</td><td>".$rowJoin['quantity']."</td><td>".$rowJoin['price']."</td><td>pc</td><td>".($rowJoin['price']*$rowJoin['quantity'])."</td></tr>";
			}
		
		?>
		<tr>
			<td><br></td>
		</tr>
		<tr>
			<td>Total</td>
			<td></td>
			<td></td>
			<td></td>
			<td><?php echo $subTotal ?></td>
		</tr>
		<tr>
			<td>Amount Chargable(In words)<br> Rs. <?php AmountInWords((float)$subTotal); ?> </td>
		</tr>
		<tr>
			<td>Signed By :</td>
			<td colspan="4">Received By :</td>
		</tr>
	</table>
		
	<?php }}
}
function AmountInWords(float $amount)
{
   $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
   // Check if there is any number after decimal
   $amt_hundred = null;
   $count_length = strlen($num);
   $x = 0;
   $string = array();
   $change_words = array(0 => '', 1 => 'One', 2 => 'Two',
     3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
     7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
     10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
     13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
     16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
     19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
     40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
     70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $here_digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $x < $count_length ) {
      $get_divider = ($x == 2) ? 10 : 100;
      $amount = floor($num % $get_divider);
      $num = floor($num / $get_divider);
      $x += $get_divider == 10 ? 1 : 2;
      if ($amount) {
       $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
       $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
       $string [] = ($amount < 21) ? $change_words[$amount].' '. $here_digits[$counter]. $add_plural.' 
       '.$amt_hundred:$change_words[floor($amount / 10) * 10].' '.$change_words[$amount % 10]. ' 
       '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
        }
   else $string[] = null;
   }
   $implode_to_Rupees = implode('', array_reverse($string));
   $get_paise = ($amount_after_decimal > 0) ? "And " . ($change_words[$amount_after_decimal / 10] . " 
   " . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';
   return ($implode_to_Rupees ? $implode_to_Rupees . 'Rupees ' : '') . $get_paise;
}
?>
