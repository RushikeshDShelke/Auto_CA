<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Controller\Adminhtml\Awb;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Delhivery\Lastmile\Helper\Data;
class PrintLabel extends \Magento\Backend\App\Action
{
	 protected $resultPageFactory;
	 protected $helper;
	 
	public function __construct(
		Data $helper,
		Context $context,
		\Magento\Ui\Component\MassAction\Filter $filter,
		\Delhivery\Lastmile\Api\AwbRepositoryInterface $awbRepository,
		\Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
		
		PageFactory $resultPageFactory,
		\Magento\Theme\Block\Html\Header\Logo $logo
	){
		$this->_logo = $logo;
		$this->resultPageFactory = $resultPageFactory;
		$this->helper = $helper;
		$this->awbRepository     = $awbRepository;
		$this->filter            = $filter;
		$this->collectionFactory = $collectionFactory;
		return parent::__construct($context);
	}

    /**
     * @param \Delhivery\Lastmile\Api\Data\AwbInterface $awb
     * @return $this
     */
    public function execute()
    {
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		
		$flag = false;
		$awb=array();
		if ($collection->getSize()) {
			$apiurl = $this->helper->getApiUrl('shippinglabelAWB');
			$cl = trim($this->getScopeConfig('delhivery_lastmile/general/client_id'));
			$token = $this->getScopeConfig('delhivery_lastmile/general/license_key');
			foreach ($collection as $awbModel) {
				if($awbModel->getState() == 1)
				{
					$awb[]=$awbModel->getAwb();
				}
			//print_r($awb);echo "<pre>";
			if($awb)
			{
				$filename = implode('_',$awb).".pdf";
				$apiurl =  $apiurl.'?wbns='.implode(',',$awb);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $apiurl);
					curl_setopt($ch, CURLOPT_FAILONERROR, 1);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Token '.$token.''));
					$result = curl_exec($ch);
					curl_close($ch);
					$codesResult=json_decode($result);
					if($codesResult)
						{
							$objArray=get_object_vars($codesResult);
						}else
						{
							$objArray=array();
						}
					$datapdf="";
					$pagebreak='';
						if(array_key_exists("packages_found",$objArray) && $objArray['packages_found'])
						{
								foreach($codesResult->packages as $codes)
								{
								//$codes=$codes->packages[0];
								
										$awbModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb')->getCollection()->addFieldToFilter('awb',$codes->wbn)->getFirstItem();
										$LocationModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Location')->load($awbModel->getPickupLocationId());
										
										$orderModel = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($codes->oid);
										$shippingAddress=$this->formateAddress($orderModel->getShippingAddress());
										$methodcode = ($orderModel->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' or $orderModel->getPayment()->getMethodInstance()->getCode() == $this->getScopeConfig('delhivery_lastmile/general/cod_method')) ? "COD" :"Pre-Paid";
										$currency = $this->_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($orderModel->getOrderCurrencyCode());
										$currencySymbol = "Rs.";//$currency->getCurrencySymbol();
										$shipDates='';
										$itemRow="";
										$grand_total=0;
										$shipping_amount=0;
										$order_Items = $orderModel->getItemsCollection();
										foreach($orderModel->getShipmentsCollection() as $shipment){
											$shipDates = $shipment->getCreatedAt();
											if($awbModel->getShipmentId()==$shipment->getIncrementId())
											{
												$shipping_amount+=$awbModel->getShippingAmount();
												foreach ($shipment->getAllItems() as $item) {    
													foreach ($order_Items as $order_Item){
														if($order_Item->getItemId() == $item->getOrderItemId()){
															$discount=$order_Item->getDiscountAmount();
															$order_item_qty=$order_Item->getQtyOrdered();
															$one_item_discount=$discount/$order_item_qty;
															$total_item_price=$order_Item->getRowTotalInclTax();
															$one_item_price_including_tax=$total_item_price/$order_item_qty;
															$total_discount=$one_item_discount*$item->getQty();
															$total_item_price=$one_item_price_including_tax*$item->getQty();
															$grand_total+=$total_item_price-$total_discount;
															$itemRow.='<tr>
															<td>'.$item->getName().'</td>
															<td>'.$currencySymbol.number_format($one_item_price_including_tax,2).'</td>
															<td>'.number_format($item->getQty(),2).'</td>
															<td>'.$currencySymbol.number_format($total_item_price,2).'</td>
															</tr>';
														}
													}
												}
											}
											
										}//echo "dsdd";die;
										$grand_total+=$shipping_amount;
										$itemRow.='<tr>
												<td colspan="3" align="right"><strong>Shipping & Handling&nbsp;</strong></td>
												
												<td>&nbsp;'.$currencySymbol.number_format($shipping_amount,2).'</td>
											  </tr>';
											  $itemRow.='<tr>
												<td colspan="3" align="right"><strong>Total&nbsp;</strong></td>
												
												<td><strong>&nbsp;'.$currencySymbol.number_format($grand_total,2).'</strong></td>
											  </tr>';
										
										//die;
										//	echo $awbModel->getAwb();
										$datapdf.=$pagebreak.'<table width="384" border="1" cellpadding="5">
										  <tr>
											<td width="162" align="center"><img src="'.$this->_logo->getLogoSrc().'" height="30px"></td>
											<td width="206" align="center"><img src="'.$codes->delhivery_logo.'" height="30px"></td>
										  </tr>
										  <tr>
											<td><img src="'.$codes->barcode.'" height="60px"/></td>
											<td>Order Id:&nbsp;'.$codes->oid.'</td>
										  </tr>
										  <tr>
											<td><strong>Shipping Address :</strong><br>'.$shippingAddress.'</td>
											<td align="center"><strong>'.$methodcode.'</strong></td>
										  </tr>
										  <tr>
											<td><strong>Seller:</strong>&nbsp;'.$LocationModel->getName().'<br>'.$LocationModel->getAddress().'</td>
											<td>GST: '.$this->getScopeConfig('delhivery_lastmile/general/gst_no').'<br>
												TIN: '.$this->getScopeConfig('delhivery_lastmile/general/consignee_tin').'<br>
												CST: '.$this->getScopeConfig('delhivery_lastmile/general/cst_no').'<br>
												Dt.:'.date('d/m/Y',strtotime($shipDates)).'
											</td>
										  </tr>
										  <tr>
											<td colspan="2">
											<table border="1" width="100%" cellspacing="0" cellpadding="3">
											  <tr>
												<td><strong>Product</strong></td>
												<td><strong>Price</strong></td>
												<td><strong>Qty</strong></td>
												<td><strong>Total</strong></td>
											  </tr>'.$itemRow.'
											</table>
											</td>
										  </tr>
										  <tr>
											<td>Return Address:&nbsp;'.$this->getScopeConfig('delhivery_lastmile/general/return_address').'</td>
											<td><img src="'.$codes->barcode.'" height="60px"/></td>
										  </tr>
										</table>';
										$pagebreak='<br pagebreak="true" />';
								}
								//echo $datapdf;die;
							//$this->getPdf($datapdf);
							$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
							require_once $directory->getRoot().'/src/tcpdf.php';
							$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
							$pdf->AddPage();
							$pdf->writeHTML($datapdf, true, false, true, false, '');
							$pdf->lastPage();
							//header('Content-type: application/pdf');
							//header('Content-Disposition: attachment; filename="delhivery"'.time().'".pdf"');
							 $omanager = \Magento\Framework\App\ObjectManager::getInstance();
							 $filesystem = $omanager->get('Magento\Framework\App\Filesystem\DirectoryList');
							 $varPath = $filesystem->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)."/delhivery/";
							 if (!file_exists($varPath)) {
									mkdir($varPath, 0777, true);
								}
							 $fileNL = $varPath.$filename;
							 $pdf->Output($fileNL,'F');
							 $pdf->Output($filename,'D');
						}else{
							$this->messageManager->addErrorMessage(__('No Shipping label available at this moment.')); 
							$resultRedirect = $this->resultRedirectFactory->create();
							$resultRedirect->setPath('delhivery_lastmile/awb');
							return $resultRedirect;
						}
			}else
			{
				$this->messageManager->addErrorMessage(__('Something went wrong')); 
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('delhivery_lastmile/awb');
				return $resultRedirect;
			}
		}
		}
		else
			{
				$this->messageManager->addErrorMessage(__('No AWB selected for print label.')); 
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('delhivery_lastmile/awb');
				return $resultRedirect;
			}
		
	}
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
	 
	 public function getPdf($data)
	 { 
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		require_once $directory->getRoot().'/src/tcpdf.php';
		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->AddPage();
		$pdf->writeHTML($data, true, false, true, false, '');
		$pdf->lastPage();
		return $pdf->Output('delhivery'.time().'.pdf', 'I');
	 }
	 
	 public function formateAddress($ship)
	 {
		 $address=$ship->getFirstname()." ".$ship->getLastname().",<br>";
		 foreach($ship->getStreet() as $street)
		 {
		 	$address=$address.$street.",<br>";
		 }
		 $address=$address.$ship->getCity().", ".$ship->getRegion().", ".$ship->getPostcode()."<br>";
		 $address=$address."Ph.: ".$ship->getTelephone();
		 return $address;
	 }
}
