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
class MassManifest extends \Magento\Backend\App\Action
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
		array $data = []
	){
		$this->resultPageFactory = $resultPageFactory;
		$this->awbRepository     = $awbRepository;
		$this->helper = $helper;
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
        $pickupLocationId=$this->getRequest()->getParam('address');
		//$waybills = $this->getRequest()->getParam('selected');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$locationModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Location')->load($pickupLocationId);
		
		$url = $this->helper->getApiUrl('manifestAWB');
		$clientid = $this->getScopeConfig('delhivery_lastmile/general/client_id');
		$token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
		if($clientid && $token && $url)
		{
			$token = "$token"; // replace this with your token key
			$url .= "cmu/push/json/?token=".$token;
			$succsscount = 0;
			$failcount = 0;
			$msg = '';
			$totalQtyOrdered=0;
			$errMsg='';//echo "<pre>";
			foreach ($collection as $awbModel) {
				//$awbModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb')->load($waybill);
				if($awbModel->getStatus() == 'Assigned'){
					$orderModel = $this->_objectManager->create('Magento\Sales\Model\Order')->load($awbModel->getOrderid());
					$address = $orderModel->getShippingAddress();
					$params = array(); // this will contain request meta and the package feed
					$package_data = array(); // package data feed
					$shipments = array();
					$ordered_items = $orderModel->getAllItems();
					$categoryName=array();
					$item_desc="";
					$grand_total=0;
					$order_qty=0;
					$shipping_amount=0;
					$total_tax=0;
					$order_Items = $orderModel->getItemsCollection();
					foreach($orderModel->getShipmentsCollection() as $shipment){
						if($awbModel->getShipmentId()==$shipment->getIncrementId())
						{
							$shipping_amount+=$awbModel->getShippingAmount();
							foreach ($shipment->getAllItems() as $item) { 
								foreach ($order_Items as $order_Item){
									if($order_Item->getItemId() == $item->getOrderItemId()){
										$product = $objectManager->create('Magento\Catalog\Model\Product')->load($order_Item->getProductId());
										$item_desc.= $product->getName().',';
										$categoryIds = $product->getCategoryIds();
										$categoryName = array();
										if (isset($categoryIds[0])){
										$category = $objectManager->create('Magento\Catalog\Model\Category')->load($categoryIds[0]);
										$categoryName[] = $category->getName();
										}
										$discount=$order_Item->getDiscountAmount();
										$order_item_qty=$order_Item->getQtyOrdered();
										$total_item_tax=$order_Item->getTaxAmount();
										$one_item_tax=$total_item_tax/$order_item_qty;
										$total_tax=$one_item_tax*$item->getQty();
										$total_item_price=$order_Item->getRowTotalInclTax();
										$one_item_price_including_tax=$total_item_price/$order_item_qty;
										$one_item_discount=$discount/$order_item_qty;
										$total_discount=$one_item_discount*$item->getQty();
										$row_total=$one_item_price_including_tax*$item->getQty();
										$order_qty +=$item->getQty();
										$grand_total+=$row_total-$total_discount;
									}
								}
							}
						}
											
					}
					//echo $shipping_amount;
					//echo $grand_total;
					$grand_total+=$shipping_amount;
					//echo 'As='.$grand_total;die;
					$methodcode = ($orderModel->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' or $orderModel->getPayment()->getMethodInstance()->getCode() == $this->getScopeConfig('delhivery_lastmile/general/cod_method')) ? "COD" :"Pre-Paid";
					$codamount = ($orderModel->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' or $orderModel->getPayment()->getMethodInstance()->getCode() == $this->getScopeConfig('delhivery_lastmile/general/cod_method')) ? $grand_total : "00.00";
					$shipment = array();
					$shipment['client'] = $clientid;
					$shipment['name'] = $address->getName(); // consignee name
					$shipment['order'] = $orderModel->getIncrementId(); // client order number
					$shipment['products_desc'] = $item_desc;
					$shipment['order_date'] = $orderModel->getUpdatedAt(); // ISO Format
					$shipment['payment_mode'] = $methodcode;
					$shipment['total_amount'] = $grand_total; // in INR
					$shipment['cod_amount'] = $codamount; // amount to be collected, required for COD
					$shipment['add'] = $address->getStreet(); // consignee address
					$shipment['city'] = $address->getCity();
					if($address->getRegion())
					$shipment['state'] = $address->getRegion();
					$shipment['waybill'] = $awbModel->getAwb();
					$shipment['shipment_length'] = $awbModel->getShipmentLength();
					$shipment['shipment_width'] = $awbModel->getShipmentWidth();
					$shipment['shipment_height'] = $awbModel->getShipmentHeight();
					$shipment['country'] = $address->getCountry();
					if($address->getTelephone())
					$shipment['phone'] = $address->getTelephone();
					$shipment['pin'] = $address->getPostcode();
					$shipment['return_add'] = $this->getScopeConfig('delhivery_lastmile/general/return_address');;
					//$order_qty = floor($orderModel->getData('total_qty_ordered')); //get qty of all items rounded to full number (without 3.0000 or so)
					$shipment['quantity'] = $order_qty; #quantity of goods, it has to be positive integer .
					$shipment['consignee_tin'] = $this->getScopeConfig('delhivery_lastmile/general/consignee_tin'); #consignee tin no
					$shipment['commodity_value'] = $grand_total;#commodity value
					$shipment['tax_value'] = $total_tax; #tax value
					$shipment['sales_tax_form_ack_no'] = $this->getScopeConfig('delhivery_lastmile/general/sale_tax_form'); #Sale Tax Form Acknowledge No.
					$shipment['category_of_goods'] = implode(',',array_unique($categoryName)); #Category of goods
					//echo "<pre>";print_r($shipment);die;
					$shipments = array($shipment);
					
					//------------ Add Pickup Location 
					
					
					$pickupLocation = array();
					$pickupLocation['add']=$locationModel->getAddress();
					$pickupLocation['city']=$locationModel->getCity();
					$pickupLocation['country']='India';
					$pickupLocation['name']=$locationModel->getName();
					$pickupLocation['phone']=$locationModel->getPhone();
					$pickupLocation['pin']=$locationModel->getPin();
					
					$package_data['pickup_location'] = $pickupLocation;
					$package_data['shipments'] = $shipments;
					/////////////end: building the package feed/////////////////////
					$params['format'] = 'json';
					$params['data'] = json_encode($package_data);
					//echo "<pre>";print_r($params);die;
					$result = $this->helper->Executecurl($url,'post',$params);
					$result = json_decode($result);
					//print_r($params);echo"<pre><br>----------<br>";print_r($result);die;
					if($result)
					{
						$objArray=get_object_vars($result);
					}else
					{
						$objArray=array();
					}
					//echo "<pre>";print_r($objArray);die;
					if(array_key_exists("success",$objArray) && $objArray['success']){
					$awbModel->setPickupLocationId($pickupLocationId);
					$awbModel->setReturnAddress($this->getScopeConfig('delhivery_lastmile/general/return_address'));
					$awbModel->setStatusType('UD');
					$awbModel->setData('status','Manifested');
					$awbModel->save();
					
					$totalQtyOrdered+=intval($result->package_count);
					$upload_wbn=($result->upload_wbn)?' Upload WBN is: '.$result->upload_wbn.'.':'';
					$this->messageManager->addSuccessMessage(__("#".$awbModel->getAwb()." AWB Submitted Successfully.".$upload_wbn));
					$succsscount++;	
					}
					else{
						if(array_key_exists("error",$objArray))
						{
							$this->messageManager->addErrorMessage(__($result->error));
						}
						elseif($result)
						{
							$upload_wbn=(array_key_exists("upload_wbn",$objArray))?' Upload WBN is: '.$result->upload_wbn.'.':'';
							try{
							foreach($result->packages as $Rdata)
							{
								
								$this->messageManager->addErrorMessage(__($Rdata->remarks));
								
							}
							}catch(Exception $e){
									$this->messageManager->addErrorMessage(__("Something went wrong. please connect to support."));
									$resultRedirect = $this->resultRedirectFactory->create();
									$resultRedirect->setPath('delhivery_lastmile/awb');
									return $resultRedirect;
								}
							
							//$errMsg .= "".$result->packages[0]->status."! #".$awbModel->getAwb()." AWB Failed to Submit Manifest. Remark: ".$result->packages[0]->remarks[0].".".$upload_wbn."<br />";
							
						}else
						{
							$this->messageManager->addErrorMessage(__("Something went wrong. please connect to support."));
						}
						$failcount++;
					}
				}else
				{
					if($awbModel->getState() == "1")
					{
						$this->messageManager->addErrorMessage(__("#".$awbModel->getAwb()." AWB Already Submitted or Something Went Wrong."));
					}else
					{
						$this->messageManager->addErrorMessage(__("#".$awbModel->getAwb()." Unused AWB can not be submit"));
					}
				}
			}
			
			if($totalQtyOrdered>0){
				$previewOrderedQty=$locationModel->getExpectedPackageCount();
				$previewOrderedQty+=$totalQtyOrdered;
				$locationModel->setExpectedPackageCount($previewOrderedQty);
				$locationModel->save();
			}
			
			if($succsscount){
				$this->messageManager->addSuccessMessage(__("$succsscount Waybills Submited Successfully."));
			}
			
			
		}
		else
		{
			$this->messageManager->addErrorMessage(__('Please add valid License Key and Gateway URL in plugin configuration'));
		}	
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('delhivery_lastmile/awb');
		return $resultRedirect;
    }
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
