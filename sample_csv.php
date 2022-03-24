<?php 
$invoice_no = 0;
$challan_no =0;
$warehouse = 0;
$sku = 'AD1CMTEST0001';
$supplier_list = 0;
?>
<form action="csv_download.php" method="post">
	<input type="hidden" name="csv_invoice_no" value="<?php echo $invoice_no ?>" />
	<input type="hidden" name="csv_challan_no" value="<?php echo $challan_no ?>" />
	<input type="hidden" name="csv_warehouse" value="<?php echo $warehouse ?>" />
	<input type="hidden" name="csv_sku" value="<?php echo $sku ?>" />
	<input type="hidden" name="csv_supplier_name" value="<?php echo $supplier_list ?>" />
	<input type="submit" name="csv_download" value="Download CSV" />
</form>
