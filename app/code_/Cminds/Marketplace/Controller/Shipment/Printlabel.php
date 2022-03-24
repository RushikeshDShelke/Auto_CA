<?php

namespace Cminds\Marketplace\Controller\Shipment;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Model\Pdf;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Shipment\Track as SalesOrderTrack;
use Magento\Store\Model\StoreManagerInterface;

class Printlabel extends AbstractController
{
    protected $registry;
    protected $track;
    protected $pdf;
    protected $fileFactory;
    protected $dateTime;
    protected $shipment;

    public function __construct(
        Context $context,
        Data $helper,
        SalesOrderTrack $track,
        Pdf $pdf,
        FileFactory $fileFactory,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->track = $track;
        $this->pdf = $pdf;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
    }

    // public function execute()
    // {
    //     if (!$this->canAccess()) {
    //         return $this->redirectToLogin();
    //     }

    //     echo $id = $this->getRequest()->getParam('id'); exit;

    //     try {
    //         $track = $this->track->load($id);

    //         $model = $this->pdf;

    //         $shipments = [];
    //         $shipments[] = $track->getShipment();

    //         if ($track) {
    //             $model->setOrderId($track->getOrderId());
    //             $model->setCarrier($track->getCarrierCode());

    //             $pdf = $model->getPdf($shipments);

    //             return $this->fileFactory->create(
    //                 'label-' . $this->dateTime->date('Y-m-d_H-i-s') . '.pdf',
    //                 $pdf->render(),
    //                 DirectoryList::UPLOAD
    //             );
    //         }
    //     } catch (LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     }

    //     $this->_redirect('*/order');
    // }


      public function execute()
    {

        $shipmentId = $this->getRequest()->getParam('id');
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $shippingCarrier = '';

        $baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
//        $baseUrl = 'http://127.0.0.1/craftmaestro_new/';
        //$currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');   
        $supplierId = $this->_objectManager->create('Cminds\Supplierfrontendproductuploader\Helper\Data')->getSupplierId();
        if(!$supplierId)
	{
		$customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
    		if($customerSession->isLoggedIn()) {
            		echo $supplierId = $customerSession->getCustomer()->getId(); die; // get ID
    		}
	}
        if ($shipmentId) {            
           
            if ($shipment = $this->_objectManager->create(\Magento\Sales\Model\Order\Shipment::class)->load($shipmentId)) {

                $order = $shipment->getOrder();
		$payment = $order->getPayment();
    		$method = $payment->getMethodInstance();
		$cod = false;
		if($method->getCode()=='checkmo'){
			$cod = true;	
		}
                $orderdate = date('d/M/y', strtotime($order->getCreatedAt()));
                $increment_id = $order->getIncrementId();
                $mobile = $order->getShippingAddress()->getTelephone();
                $username = $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
                $shippingAddress = $order->getShippingAddress();
                $billingAddress = $order->getBillingAddress();
                $billName = $billingAddress->getName();
                $bill_street = $order->getBillingAddress()->getData('street');
                //$bill_street =$order->getBillingAddress()->getStreetFull();

                $bill_city = $order->getBillingAddress()->getCity();
                $bill_postco577de = $order->getBillingAddress()->getPostcode();
                $bill_state = $order->getBillingAddress()->getRegion();
                $custemail=$shippingAddress->getEmail();
                $custname=$shippingAddress->getName();
                $street = $order->getShippingAddress()->getData('street');
                $mcamount = $order->getShippingAmount();
                $city = $order->getShippingAddress()->getCity();
                $postcode = $order->getShippingAddress()->getPostcode();
                $state = $order->getShippingAddress()->getRegion();
                $getCountryId = $order->getShippingAddress()->getCountryId();

                $ewayBillNo = '';
                $airwayBillNo = '';
		$shipDate = '';
                foreach($order->getShipmentsCollection() as $shipment)
                {
		    $shipDate = $shipment->getCreatedAt();
                    $tracks = $shipment->getTracksCollection(); 
                    foreach ($tracks as $track)
                    {
                        $trackingInfos = $track->getData();
                        $shippingCarrier=$trackingInfos["carrier_code"];
                        $airwayBillNo = $trackingInfos["track_number"];
                    }
                    if(count($shipment->getData())){
                        if($shipment->getIncrementId()){
                            try{
                            //echo $shipment->getIncrementId();    
                            $eObj = $this->_objectManager->create('Ced\Delhivery\Model\Awb')->load($shipment->getIncrementId(),'shipment_id');
                            ////echo '<pre>'; print_r($eObj->getData()); echo '</pre>';
                            $ewayBillNo = $eObj->getAwb();

                            }
                            catch(\Exception $ex){
                            
                            }
                        }
                    }
                }
                $itemdetails='';
		$itemNames = "";
                $mcitemsVisible = $order->getAllVisibleItems();
                $mcamount = $order->getShippingAmount();
                //$mcshipamt=Mage::helper('core' )->currency($mcamount);
                $mcshipamt=$mcamount;
                $mcin=1;
                $productinfo='';
                $itemdetailscustomer='';
                $invoicesent=0;
                $taxamt=0;
                $taxpercent=0;
                $grandtotal=0;
                $invoiceid='';
                $invoicedate='';
                $invid='';
                $customer_invoice_number=0;
                if ($order->hasInvoices()) {
                $taxclassper = $invIncrementIDs = array();

                foreach ($order->getInvoiceCollection() as $inv) {
                    $invIncrementIDs[] = $inv->getIncrementId();
                    $invid=$inv->getId();
                    $invoiceid=$inv->getIncrementId();
                    //echo $inv->getCreatedAt();
                    $invoicedate=date('d/M/y', strtotime($inv->getCreatedAt()));

                   $invoice_collection = $this->_objectManager->create('Kellton\Custominvoice\Model\Customcinvoice')->getCollection();
                   $invoice_collection = $invoice_collection->addFieldToSelect("*")
                   ->addFieldToFilter('invoice_number', array('eq'=>$invoiceid));
                   $customer_invoice_number = $invoiceid;   
                   //echo '<pre>'; print_r($invoice_collection->getData()); echo '</pre>';

                   foreach ($invoice_collection as $key => $invoicecm) {
                    //echo '<pre>'; print_r($invoicecm); echo '</pre>';
                      if($invoicecm['custom_invoice_number']!=''){
                            $customer_invoice_number = $invoicecm['custom_invoice_number'];
                      }
                    } 
                }
            }
                $tax_info = $order->getFullTaxInfo();
                $itemdetails ='';
                $rowtotal = 0;
                foreach ($mcitemsVisible as $item){
                $productload = $this->_objectManager->create('Magento\Catalog\Model\ProductRepository')->get($item->getSku());
                $suppId= $productload->getData('creator_id');
        if ($supplierId == $suppId) 
        {
                $hsn=$productload->getHsn();
                $rowtotal=$productload->getPrice()*$item->getQtyOrdered();
                $rowtotal = $rowtotal - $item->getDiscountAmount();
                $productprice = $priceHelper->currency($productload->getPrice(), true, false);

                //$suppId= $productload->getData('creator_id');
                $invoicesent=$item->getInvoicesent();

                $taxCalculation = $this->_objectManager->create('\Magento\Tax\Model\Calculation');
                $request        = $taxCalculation->getRateRequest(null, null, null, 1);
                $taxClassId     = $productload->getTaxClassId();
                $taxpercent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
                $taxclassper[] = $taxpercent;
                $taxamount = $rowtotal*$taxpercent;
                $taxamounts=$taxamount/100;
                $taxamt=$taxamounts;
                $rowtotal = $rowtotal+$taxamt;
                $grandtotal=$grandtotal+$rowtotal;
                $rowtotalamt=$priceHelper->currency($rowtotal, true, false);

                $IGST                = 0;
                $CGST                = 0;
                $SGST                = 0;

                $region              = $this->_objectManager->create('Magento\Directory\Model\Region')->load($order->getShippingAddress()->getRegion(), 'default_name');
                $state_code          = $region->getCode();//code);
                if ($state_code == "HR") {
                    $IGST = $priceHelper->currency(0, true, false);
                    $SGST = $priceHelper->currency($taxamt / 2, true, false);
                    $CGST = $priceHelper->currency($taxamt / 2, true, false);
                } else {
                    $IGST = $priceHelper->currency($taxamt, true, false);
                    $SGST = $priceHelper->currency(0, true, false);
                    $CGST = $priceHelper->currency(0, true, false);
                }
		$itemNames .= $item->getName();
                $itemdetails .= '<tr style="border:1px solid  #000;" class="invoice-description">
                                        <td style="border:1px solid  #000;" >'.$item->getName().' Qty '.intval($item->getQtyOrdered()).'</td>
					<td style="border:1px solid  #000;">'.$rowtotalamt.'</td>
                                        <td style="border:1px solid  #000;">'.$rowtotalamt.'</td>
                                    </tr>';
                $productinfo.='<tr><td width="60">Name</td><td>'.$item->getName().'</td></tr><tr><td width="60">Quantity</td><td>'.intval($item->getQtyOrdered()).'</td></tr>';
                $mcin++;
             }
            }
                /*$shippingamount      = $order->getShippingAmount();
                $shippingTax         = $order->getShippingTaxAmount();
		
		if($taxclassper)
                $shippingTaxpercent  = max($taxclassper);
                $SIGST = $priceHelper->currency(0, true, false);
                $SSGST = $priceHelper->currency(0, true, false);
                $SCGST = $priceHelper->currency(0, true, false);

                if($shippingTax > 0){
                if ($state_code == "HR")
                 {
                $SIGST = $priceHelper->currency(0, true, false);
                $SSGST = $priceHelper->currency($shippingTax / 2, true, false);
                $SCGST = $priceHelper->currency($shippingTax / 2, true, false);
      
                $SIGST = $priceHelper->currency($shippingTax, true, false);
                $SSGST = $priceHelper->currency(0, true, false);
                $SCGST = $priceHelper->currency(0, true, false);
                }
                }
                else{
                $shippingTax = 0;
                $shippingTaxpercent = 0;
                $SIGST = $priceHelper->currency(0, true, false);
                $SSGST = $priceHelper->currency(0, true, false);
                $SCGST = $priceHelper->currency(0, true, false);
                }
                $grandtotal  = $grandtotal + $shippingTax;
                $shippingTax = $priceHelper->currency($shippingTax, true, false);
		*/
                ?>
                <!DOCTYPE html>
                <html lang="en-US">

                <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1,
                    maximum-scale=1, user-scalable=no">

                <title>Shipment PDF Format</title>
                <meta name="description" content="content">
                <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">

                <!-- <style>
                   .page-print{width:100%;max-width:757px;margin:20px;background:#fff}@media screen,print{body,h1,h2,h3,h4,h5,h6,p,strong,table.body,td,th{font-family:lato;font-weight:400}body{margin:0;padding:0;text-align:left;color:#333}/ body{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table{mso-table-lspace:0;mso-table-rspace:0}img{-ms-interpolation-mode:bicubic;max-width:100%}body{margin:0;padding:0}table,table td{border-collapse:collapse}table td{vertical-align:top}a{color:#3696c2;text-decoration:none}li,ul{list-style:none;margin:0}ul{margin-block-start:0;margin-block-end:0;padding-inline-start:0;margin-bottom:10px}.border-red{border:2px dotted red;padding:15px 25px}.bar-code,.invoice-info,.top-heading{border:2px solid #000}.logo-wrap{padding:10px;border-right:2px solid #000;vertical-align:middle;text-align:center}.logo-wrap a{display:inline-block}.logo-wrap a img{width:50px;max-height:60px}.top-heading .heading{padding:10px}table h1{font-size:16px;text-transform:uppercase;margin:0 0 10px}.delhivery-logo{padding:10px}.delhivery-logo img{width:120px}.bar-code td{padding:3px 5px}.return-barcode img{width:180px}.invoice-info h2{font-size:16px;text-transform:uppercase;margin:0 0 10px}.invoice-info p{margin:0 0 5px}.invoice-info li{display:inline-block;margin-right:10px}.invoice-info li span{font-weight:600}.invoice-info strong{font-size:12px;font-weight:700;text-transform:uppercase;text-decoration:underline}.invoice-info table td{padding:3px 5px;border-bottom:2px solid transparent}.invoice-info .invoice-detail td{border-bottom:2px solid #000}.bill-wrap td,.invoice-description td{border:2px solid #000;padding:3px 5px}.invoice-description .bottom-none{border-bottom:2px solid transparent}.invoice-description td{text-align:center}.bill-wrap table td{border:none}.bill-wrap .gst-value td{border:2px solid #000;padding:3px 5px}.bill-wrap p{margin:0 0 2px}.thanks{padding:3px 5px 0;background-color:#d8d8d8}}
                </style> -->
                </head>

                <body class="page-print">
                <!--Page Wrapper Start-->
                <div id="wrapper">

                <!--Header Section Start-->

                <header id="header">
                    <table cellpadding="0" cellspacing="0" border="1" width="40%" align="center">
                        <tr>
                            <td class="border-red">
                                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                    <tr>
                                        <td colspan="3">
                                            <table class="top-heading" cellpadding="0" cellspacing="0" border="1" width="100%">
                                                <tr>
                                                    <td class="logo-wrap" border="1" style="text-align:center">
                                                        <a href="#">
								<h2>Craft Maestros</h2>
                                                           <!--<img src="<?php echo $baseUrl.'/css/printship/images/mailer-logo.png' ?>" alt="logo">-->
                                                        </a>
                                                    </td>
					    	<!--<td class="heading" align="left">
							<h1> <b>AMMARA CRAFT MAESTROS PVT. LTD</b>
                                                    </td>-->
                                                    <td class="delhivery-logo" align="center">
                                                        <a href="#">
                                                            <?php //Delhivery
                                                            if($shippingCarrier == "dlastmile" || $shippingCarrier == "custom"){ ?>
                                                            <img src="<?php echo $baseUrl.'images/delhivery.png'?>" alt="delhivery">
                                                            <?php } if($shippingCarrier == "pudo"){ ?>
                                                                <img src="<?php echo $baseUrl.'pub/media/css/printship/images/dtdc.png'?>" alt="DTDC">
                                                            <?php } ?>    
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="10"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <table class="bar-code" cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr style="text-align:center;">
                                                    <td class="code" style="text-align:center;" colspan="2">
                                                    <?php  $code = $airwayBillNo; ?>
                                                    <img alt='barcode' src='https://www.craftmaestros.com/barcode/barcode.php?codetype=Code39&size=50&text=<?php echo $code; ?>&print=true'/>
                                                    </td>
						</tr>
					    </table>
				    	</td>
                                     </tr>
                                     <tr>
                                        <td><?php echo $postcode ?></td>
					<td colspan="2" align="center"><?php echo $city ?></td>	
                                     </tr>
                                     <tr>
                                        <td style="padding:0;">
                                        	<table cellpadding="0" cellspacing="0" border="1" width="100%" class="ship-detail">
                                                	<tr>
                                                               	<td style="border:1px solid  #000;">
                                                                    <b>Ship Address:</b></br>
								<?php echo "<b>".$username."</b><br>"; echo $street.",".$city.",". $postcode;?>
                                                                </td>
							</tr>
						</table>
					</td>
					<td border="1" colspan="2" style="border:1px solid#000;text-align:center;"><?php if($cod) { echo "COD Surface"; } else { echo "Pre-Paid Surface"; }?></td>
				     </tr>
				     <tr style="border:1px solid  #000;" border="1">
					<td style="border:1px solid  #000;">Seller: Ammara Craft Maestros Pvt Ltd
Address: B-1802, Pioneer park, Golf course extn
road,, sector 61, gurgaon haryana 122101,
Gurgaon, HARYANA, 122101, India.
</td>
					<td style="border:1px solid  #000;" border="1" colspan="2">Dt.: <?php echo $shipDate ?></td>
				     </tr>
				     <tr border="1">
					<td style="border:1px solid  #000;">Product</td>
					<td style="border:1px solid  #000;">Price</td>
					<td style="border:1px solid  #000;">Total</td>
				     </tr>
					<?php
						echo $itemdetails;				
					?>
				     <tr style="border:1px solid  #000;">
					<td style="border:1px solid  #000;">Total</td>
					<td style="border:1px solid  #000;"><?php echo $priceHelper->currency($grandtotal, true, false); ?></td>
					<td style="border:1px solid  #000;"><?php echo $priceHelper->currency($grandtotal, true, false); ?></td>
				     </tr>
				     <tr>
                                        <td height="10"></td>
                                    </tr>
				     <tr style="text-align:center;">
                                       	<td colspan="3" style="text-align:center;">
	                                        <img alt='barcode' src='https://www.craftmaestros.com/barcode/barcode.php?codetype=Code39&size=50&text=<?php echo $itemNames; ?>&print=true'/>
                                   	</td>
                                     </tr>
				     <tr>
					<td style="border:1px solid  #000;" colspan="3">Return Address: C 1524 Basement, Block C, Sushant Lok, Phase 1, Gurgaon,
HARYANA ,India 122101,,, - Gurgaon - Haryana - 122101</td>
				     </tr>
				</table>
			      </td>
			   </tr>








							<!--<td width="203" style="border-top:2px solid#000;border-left:2px solid #000;"> <b>Product</b>
							</td>
                                                                <td width="203" style="border-top:2px  solid#000;border-left:2px solid#000;"><b>Return Code</b>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td width="203" style="border-top:2px  solid#000;border-right:2px solid#000;">
                                                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">

                                                                        <tr>
                                                                            <td width="60">Name</td>
                                                                            <td><?php echo $username?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="60">Address</td>
                                                                            <td><?php echo $street ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="60">City</td>
                                                                            <td><?php echo $city ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="60">Pin</td>
                                                                            <td><?php echo $postcode ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="60">Tel</td>
                                                                            <td><a href="tel:"><?php echo $mobile ?></a></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td style="border-top:2px solid transparent;"></td>
                                                                <td style="border-top:2px solid #000;border-left:2px solid#000;">
                                                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                                            <?php echo $productinfo; ?>
                                                                    </table>
                                                                </td>
                                                                <td style="border-top:2px solid #000;border-left:2px solid #000;">
                                                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="return-barcode">
                                                                        <tr>
                                                                            <td>
                                                                                <img src="<?php echo $baseUrl.'/css/printship/images/barcode.png'?>" alt="barcode">
                                                                            </td>

                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                </tr>-->
                                         
                            
                        <!--<tr>
                            <td style="padding: 10px 25px;">
                                <table cellpadding="0" cellspacing="0" border="0" width="100%" class="invoice-info">
                                    <tr>
                                        <td colspan="6" width="330" style="padding: 10px 10px 10px 20px;">
                                            <h2> <b>AMMARA CRAFT MAESTROS PVT. LTD</b>
                                            </h2>
                                            <p>
                                                B-1802, Pioneer Park,
                                            </p>
                                            <p>
                                                Golf Course extn. Road Secotor 61, Gurgaon 122001, Haryana, India.
                                            </p>
                                            <ul>
                                                <li> <span>Tel :</span> <a href="tel:+911244307447">+91
                                                                124-4307447;</a> </li>
                                                <li><span> Email :</span> <a href="mailto:
                                                                accounts@craftmaestros.com">
                                                                accounts@craftmaestros.com</a></li>
                                            </ul>
                                            <strong>
                                                        CIN: U74999HR2018PTC075480
                                                    </strong>
                                        </td>
                                        <td colspan="3" width="100" style="border-left:2px solid #000;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td>Invoice No.:</td>
                                                </tr>
                                                <tr>
                                                    <td>Invoice Date:</td>
                                                </tr>
                                                <tr>
                                                    <td>GSTN:</td>
                                                </tr>
                                                <tr>
                                                    <td>PAN:</td>
                                                </tr>
                                                <tr>
                                                    <td>Eway Bill No.:</td>
                                                </tr>
                                                <tr>
                                                    <td style="border-bottom:2px solid transparent;">State code: </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="3" width="160" style="border-left:2px solid #000;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" class="invoice-detail">
                                                <tr>
                                                    <td>&nbsp;<?php echo $invoiceid ?></td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;<?php echo $invoicedate ?></td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;06AARCA2483K1Z9</td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;AARCA2483K</td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;<?php echo $ewayBillNo ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="border-bottom:2px solid transparent;"><?php echo $state_code ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="invoice-description">
                                        <td width="20">Sr.No.</td>
                                        <td width="300" style="text-align: left;">Description of Product</td>
                                        <td>HSN/ SAC</td>
                                        <td>Qty</td>
                                        <td>Rate/Unit</td>
                                        <td>Discount</td>
                                        <td>Taxable</td>
                                        <td>GST</td>
                                        <td>IGST</td>
                                        <td>CGST</td>
                                        <td>SGST</td>
                                        <td>Total Amount</td>
                                    </tr>
                                    <tr class="invoice-description">
                                        <td width="20"></td>
                                        <td width="300"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>%</td>
                                        <td>val</td>
                                        <td>%</td>
                                        <td>val</td>
                                        <td>val</td>
                                        <td>val</td>
                                        <td></td>
                                    </tr>
                                    <?php echo $itemdetails ?>
                                   
                                     <tr class="bill-wrap" style="border-top:2px solid #000;">
                                        <td colspan="4" style="border-bottom:2px solid transparent;"> <b>Bill To:</b></td>
                                        <td colspan="2"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr class="bill-wrap">
                                        <td colspan="4" style="padding: 0; border-bottom:2px solid transparent;">
                                                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td width="75">Name</td>
                                                    <td><?php echo $billName ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="4"></td>
                                        <td colspan="3" align="center">Shipping Charges</td>
                                        <td align="center"><?php echo $mcshipamt ?></td>
                                    </tr>
                                    <tr class="bill-wrap">
                                        <td colspan="4" style="padding: 0; border-bottom:2px solid transparent;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td width="75">Address</td>
                                                    <td><?php echo $bill_street ?></td>
                                                </tr>
                                            </table>
                                        </td>

                                        <td colspan="4" style="padding: 0; text-align:center;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td>GST % on Shipping charges</td>
                                                    <td style="background-color: #e36c09; border-left:2px solid #000;"><?php echo $shippingTaxpercent ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="3" style="padding: 0; text-align:center;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td style="background-color: #c2d69b; ">IGST</td>
                                                    <td style="background-color: #c2d69b; border-left:2px solid #000;">CGST</td>
                                                    <td style="background-color: #c2d69b; border-left:2px solid #000;">SGST</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr class="bill-wrap">
                                        <td colspan="4" style="padding: 0; border-bottom:2px solid transparent;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td width="75">City</td>
                                                    <td><?php echo $bill_city ?></td>
                                                </tr>
                                            </table>
                                        </td>

                                        <td colspan="4" style="padding: 0; text-align:center;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td>GST (value) on Shipping charges</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="3" style="padding: 0; text-align:center;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td width="30"><?php echo $SIGST ?></td>
                                                    <td width="33" style="border-left:2px solid #000;"><?php echo $SCGST ?></td>
                                                    <td width="33" style="border-left:2px solid #000;"><?php echo $SSGST ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td style="text-align: center;"><b><?php echo $shippingTax ?></b></td>
                                    </tr>
                                    <tr class="bill-wrap">
                                        <td colspan="4" style="padding: 0; border-bottom:2px solid transparent;">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td width="75">Pin:</td>
                                                    <td><?php echo $postcode ?></td>
                                                </tr>
                                            </table>
                                        </td>

                                        <td colspan="7" style="text-align:center;"><b>Grand Total</b> </td> 
                                        <td style="text-align: center;"><b><?php echo $priceHelper->currency($mcamount+$grandtotal, true, false);?></b>
                                        </td> 
                                    </tr> 
                                    <tr class="bill-wrap">
                                     <td colspan="4" style="padding: 0; border-bottom:2px solid transparent;">
                                      <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
                                        <tr>
                                         <td width="75">GSTN:</td> 
                                         <td></td>
                                        </tr>
                                       </table>
                                       </td>
                                        <td colspan="8"></td>
                                        </tr>
                                     <tr class="bill-wrap"> <td colspan="4" style="padding: 0;
                                        border-bottom:2px solid #000;"> <table cellpadding="0" cellspacing="0"
                                        border="0" width="100%"> <tr> <td width="75">State Code:</td> <td><?php echo
                                        $bill_state ?></td> </tr> </table> </td> <td colspan="8" style="border-left:
                                        2px solid transparent;"></td> </tr>
                                     <tr class="bill-wrap">
                                      <td style="border-right:2px solid transparent">&nbsp;</td> <td colspan="11">
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%"> 
                                    <tr> <td
                                        width="200">Dispatched from: </td> <td><?php echo $bill_city ?></td> 
                                    </tr>
                                        <tr> <td width="200">Place of Supply:</td> <td><?php echo $city ?></td>
                                        </tr>
                                        </table> </td> </tr>
                                     <tr class="bill-wrap"> <td colspan="12">
                                        <u>Declaration:</u> <p>This is a system generated invoice and does not require
                                        any signatures</p> <p>Terms and Conditions applicable as per Ammara Craft
                                        Maestros website and other policies</p> </td> 
                                    </tr>
                                     <tr> <td colspan="12"
                                        height="15" style="border:2px solid transparent;"></td> </tr> <tr> <td
                                        colspan="12" align="center" class="thanks" style="border:2px solid
                                        transparent;"> <p>THANK YOU FOR YOUR BUSINESS! </p> </td> </tr> </table> </td>
                                        </tr>-->

                    </table>

                </header>
                <!--Header Section End-->
                </div>
                <!--Page Wrapper End-->

                </body>
                <script>
                window.print(); 
                </script>
                </html>
                
                <?php    
            }
        } 
    }
 } 
