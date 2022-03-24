<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dotzotfront\ShippingTracking\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;


class DotzotTrack extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::dotzottrack';

    /**
     * Generate credit memos grid for ajax request
     *
     * @return \Magento\Framework\View\Result\Layout
     * 
     * 
     */

    public function execute()
    {
  
  
        if(isset($_POST['selected']))
        {
            $no_of_selection = count($_POST['selected']);
            $orderID = implode('',$_POST['selected']);
        }
        else
        {
            $no_of_selection = '0';
        }

                if(isset($_POST['excluded']))
                {
                    $errorexclude = 'You cannot create mass / bulk dotzot tracking number, simply done one by one';
                    $this->messageManager->addError(__('Warning Reason:     '.$errorexclude)); 
                }
                else if($no_of_selection <= 1)
                {

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('sales_order'); 
                    $t1 = $resource->getTableName('sales_order_grid');
                    $t2 = $resource->getTableName('sales_order_item');
                    $t3 = $resource->getTableName('sales_order_address');
                    $newsql = "SELECT `main_table`.*, `ot`.*, `sot`.*, `ad`.* FROM " . $tableName . " AS `main_table` INNER JOIN " . $t1 . " AS `ot` ON main_table.entity_id = ot.entity_id INNER JOIN  " . $t2 . " AS `sot` ON main_table.entity_id = sot.order_id INNER JOIN " . $t3 . " AS `ad` ON main_table.entity_id = ad.parent_id WHERE (main_table.entity_id = $orderID AND ad.address_type = 'shipping')";
                    $resultdot = $connection->fetchRow($newsql);

                    if($resultdot['payment_method'] == 'cashondelivery')
                    {
                        $collect_amount = $resultdot['total_due']; 
                        $mode = 'C';
                        $totalamount = $resultdot['total_due'];
                    }      
                    else
                    {
                        $collect_amount = 0;
			            $totalamount = $resultdot['total_due']; 
                        $mode = 'P';
                    }
         
                    $check_valid = $resultdot['dotzotrack_status']; 
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $conf = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/demo_enable_mode');

                        if(($conf == '1') && ($check_valid == ''))
                        {
                            $fieldsdemo = array (
                             'Customer' => 
                                          array (
                                                  'BRCD' => '',
                                                  'CUSTCD' => 'CC000100132',
                                                ),
                             'DocketList' => 
                                          array (
                                                0 => 
                                                    array (
                                                          'AgentID' => '',
                                                          'AwbNo' => '',
                                                          'Breath' => '1',
                                                          'CPD' => '31/07/2018',
                                                          'CollectableAmount' => round($collect_amount),
                                                          'Consg_Number' => '',
                                                          'Consolidate_EW' => '',
                                                          'CustomerName' => 'Abhishek',
                                                          'Ewb_Number' => '',
                                                          'GST_REG_STATUS' => '',
                                                          'HSN_code' => '02314h03',
                                                          'Height' => '1',
                                                          'Invoice_Ref' => 'AB123X001',
                                                          'IsPudo' => 'N',
                                                          'ItemName' => 'Iphone X 246 gb black',
                                                          'Length' => '1',
                                                          'Mode' => $mode,
                                                          'NoOfPieces' => '1',
                                                          'OrderConformation' => 'Y',
                                                          'OrderNo' => 'AB123X001',
                                                          'ProductCode' => '00123',
                                                          'PudoId' => '',
                                                          'REASON_TRANSPORT' => '',
                                                          'RateCalculation' => 'N',
                                                          'Seller_GSTIN' => '123223H2',
                                                          'ShippingAdd1' => 'Plot number 14 DTDC Express limited',
                                                          'ShippingAdd2' => 'Goregaon East',
                                                          'ShippingCity' => 'Mumba',
                                                          'ShippingEmailId' => 'Abhishek.pandey@dotzot.in',
                                                          'ShippingMobileNo' => '8433985578',
                                                          'ShippingState' => 'Maharashtra',
                                                          'ShippingTelephoneNo' => '0224009038',
                                                          'ShippingZip' => '400063',
                                                          'Shipping_GSTIN' => '',
                                                      	  'TotalAmount' =>  round($resultdot['total_due']),
                                                          'TransDistance' => '',
                                                          'TransporterID' => 'TID0123',
                                                          'TransporterName' => '',
                                                          'TypeOfDelivery' => 'Home Delivery',
                                                          'TypeOfService' => 'Economy',
                                                          'UOM' => 'Per KG',
                                                          'VendorAddress1' => 'Plot number 98',
                                                          'VendorAddress2' => 'Pump House',
                                                          'VendorName' => 'ABC ltd',
                                                          'VendorPincode' => '400063',
                                                          'VendorTeleNo' => '8433985578',
                                                          'Weight' => '0.150',
                                                ),
                                              ),
                                            );
                            
                                     $headers = array('Content-Type: application/json');
                                     $ch = curl_init();
                             	 	 curl_setopt($ch,CURLOPT_URL,'http://instacom-staging.azurewebsites.net/restservice/PushOrderDataService.svc/PushOrderData_PUDO_GST');         
                            		 curl_setopt($ch,CURLOPT_POST,true);
                                     curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                                     curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                                     curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
                                     curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($fieldsdemo));
                                     $result = curl_exec($ch);
                                     curl_close($ch);     
                                     $resultdata = json_decode($result);
                                     $docketnum = $resultdata['0']->DockNo;
         
                        if($docketnum != NULL)
                        {
         
                         $sqlq = "Update " . $tableName . " Set `docketno` = '$docketnum',`dotzotrack_status` = 'success' Where `entity_id` = '$orderID'";
                         $uquery = $connection->query($sqlq);
                         
                         $store_id = $resultdot['store_id'];
                         $customer_id = $resultdot['customer_id'];
                         $total_qty = $resultdot['total_qty_ordered'];
                         $email_sent =  $resultdot['email_sent'];
                         $send_email = $resultdot['send_email'];
                         $shipping_address_id = $resultdot['shipping_address_id'];
                         $billing_address_id = $resultdot['billing_address_id'];
                         $increment_id = $resultdot['increment_id'];
                         $created_at  = $resultdot['created_at'];
                         $updated_at = $resultdot['updated_at'];
                         $packages = '[]';
                         $customer_note_notify = $resultdot['customer_note_notify'];
         
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $orderInterface = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface');
                        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByAttribute('increment_id', $increment_id);

                            if ($order->canShip())
                            {
                                                    $convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
                                                    $shipment = $convertOrder->toShipment($order);
                                    foreach ($order->getAllItems() AS $orderItem) {
                                        if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual())
                                        {
                                            continue;
                                        }
                                            $qtyShipped = $orderItem->getQtyToShip();
                                            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                                            $shipment->addItem($shipmentItem);
                            }
                                        $shipment->register();
                                        $shipment->getOrder()->setIsInProcess(true);

                                    try {
                                            $shipment->save();
                                            $shipment->getOrder()->save();
                                    
                                            // Send email
                                            //$objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                                            //    ->notify($shipment);
                                            //$shipment->save();
                                        } catch (\Exception $e)
                                        {
                                            echo "Shipment Not Created". $e->getMessage(); exit;
                                        }

                                        echo "Shipment Succesfully Generated for order: #".$increment_id;
                } else 
                {
                    echo "Shipment Not Created Because It's already created or something went wrong";
                }

                $ordert = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByAttribute('increment_id', $increment_id);
    
                if ($ordert->canInvoice()) 
                {
                        $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($ordert);
                        if (!$invoice->getTotalQty()) 
                        {
                            throw new \Magento\Framework\Exception\LocalizedException(__('You can\'t create an invoice without products.')
                            );
                        }

                            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                            $invoice->register();

                            $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder());
                        
                            $transaction->save();

                            //$this->invoiceSender->send($invoice);

                                $ordert->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                                       ->setIsCustomerNotified(true)
                                       ->save();
                }
   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableNamew = $resource->getTableName('sales_shipment'); 
 
        $sqlm = "Select `entity_id` FROM " . $tableNamew." Where `order_id` = '$orderID'";
        $trackdot = $connection->fetchRow($sqlm);  
        
        $lastid = $trackdot['entity_id'];

         $tableName1 = $resource->getTableName('sales_shipment_track'); 
         $sqlr = "Insert Into " . $tableName1 . " (`entity_id`,`parent_id`, `weight`, `qty`, `order_id`,`track_number`, `description`,`title`,`carrier_code`,`created_at`,`updated_at`) 
         Values ('','$lastid','','','$orderID','$docketnum','','Dotzot Shipping Service','custom','$created_at','$updated_at')";
         $check = $connection->query($sqlr);
         
        if($uquery)
        {
            $this->messageManager->addSuccess(__('Tracking number has been generated Successfully   '.$docketnum));
        }
        else
        {
            $this->messageManager->addError(__('Something went wrong!!!'));
            return $resultRedirect->setPath('sales/*/');
        } 
        
    }
        else
      {
         $this->messageManager->addError(__('Error Reason:     '.$resultdata['0']->Reason)); 
      }
        
        
    }  // Demo Mode
        else if(($conf == '0') && ($check_valid == '')) 
        {
        

        $cid = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/customer_id');    
            
            
                                $fieldslive = array (
                                                      'Customer' => 
                                                                      array (
                                                                        'BRCD' => '',
                                                                        'CUSTCD' => $cid,
                                                                      ),
                                                      'DocketList' => 
                                                                      array (
                                                                             0 => 
                                                                             array (
                                                              'AgentID' => '',
                                                              'AwbNo' => '',
                                                              'Breath' => '1',
                                                              'CPD' => '',
                                                              'CollectableAmount' => round($collect_amount),
                                                              'Consg_Number' => '',
                                                              'Consolidate_EW' => '',
                                                              'CustomerName' => $resultdot['customer_firstname']+' '+$resultdot['customer_middlename']+' '+$resultdot['customer_lastname'],
                                                              'Ewb_Number' => '',
                                                              'GST_REG_STATUS' => '',
                                                              'HSN_code' => '',
                                                              'Height' => '1',
                                                              'Invoice_Ref' => 'dotzot'.$resultdot['entity_id'],
                                                              'IsPudo' => 'N',
                                                              'ItemName' => $resultdot['name'],
                                                              'Length' => '1',
                                                              'Mode' => $mode,
                                                              'NoOfPieces' => '1',
                                                              'OrderConformation' => 'Y',
                                                              'OrderNo' => $resultdot['entity_id'],
                                                              'ProductCode' => $resultdot['sku'],
                                                              'PudoId' => '',
                                                              'REASON_TRANSPORT' => '',
                                                              'RateCalculation' => 'N',
                                                              'Seller_GSTIN' => '',
                                                              'ShippingAdd1' => $resultdot['shipping_address'],
                                                              'ShippingAdd2' => $resultdot['shipping_address'],
                                                              'ShippingCity' => $resultdot['city'],
                                                              'ShippingEmailId' => $resultdot['customer_email'],
                                                              'ShippingMobileNo' => $resultdot['telephone'],
                                                              'ShippingState' => $resultdot['state'],
                                                              'ShippingTelephoneNo' => $resultdot['telephone'],
                                                              'ShippingZip' => $resultdot['postcode'],
                                                              'Shipping_GSTIN' => '',
                                                          	  'TotalAmount' =>  round($resultdot['total_due']),
                                                              'TransDistance' => '',
                                                              'TransporterID' => '',
                                                              'TransporterName' => '',
                                                              'TypeOfDelivery' => 'Home Delivery',
                                                              'TypeOfService' => substr($resultdot['shipping_information'],25,10),
                                                              'UOM' => 'Per KG',
                                                              'VendorAddress1' => 'Plot number 98',
                                                              'VendorAddress2' => 'Pump House',
                                                              'VendorName' => 'ABC ltd',
                                                              'VendorPincode' => '400063',
                                                              'VendorTeleNo' => '8433985578',
                                                              'Weight' => '1.0',
                                                            ),
                                                          ),
                                                        );
                                                        
                                                     

         $headers = array('Content-Type: application/json');
         $ch = curl_init();
 	 	 curl_setopt($ch,CURLOPT_URL,'https://instacom.dotzot.in/RestService/PushOrderDataService.svc/PushOrderData_PUDO_GST');         
		 curl_setopt($ch,CURLOPT_POST,true);
         curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($fieldslive));
         $result = curl_exec($ch);
         curl_close($ch);     
         $resultdata = json_decode($result);
         
          $docketnum1 = $resultdata['0']->DockNo;
         
        if($docketnum1 != NULL)
        {
             $sqlq = "Update " . $tableName . " Set `docketno` = '$docketnum1', `dotzotrack_status` = 'success' Where `entity_id` = '$orderID'";
             $uquery = $connection->query($sqlq);
             $store_id = $resultdot['store_id'];
             $customer_id = $resultdot['customer_id'];
             $total_qty = $resultdot['total_qty_ordered'];
             $email_sent =  $resultdot['email_sent'];
             $send_email = $resultdot['send_email'];
             $shipping_address_id = $resultdot['shipping_address_id'];
             $billing_address_id = $resultdot['billing_address_id'];
             $increment_id = $resultdot['increment_id'];
             $created_at  = $resultdot['created_at'];
             $updated_at = $resultdot['updated_at'];
             $packages = '[]';
             $customer_note_notify = $resultdot['customer_note_notify'];

             $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
             $orderInterface = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface');
             $order = $objectManager->create('Magento\Sales\Model\Order')->loadByAttribute('increment_id', $increment_id);

            if ($order->canShip()) 
            {
                $convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
                $shipment = $convertOrder->toShipment($order);
                foreach ($order->getAllItems() AS $orderItem) 
                {
                    if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) 
                    {
                        continue;
                    }
                    $qtyShipped = $orderItem->getQtyToShip();
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    $shipment->addItem($shipmentItem);
                }
                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);

                    try {
                            $shipment->save();
                            $shipment->getOrder()->save();

                            // Send email
                            //$objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                            //    ->notify($shipment);
                            //$shipment->save();
                         }   catch (\Exception $e) 
                             {
                                    echo "Shipment Not Created". $e->getMessage(); exit;
                             }

                                echo "Shipment Succesfully Generated for order: #".$increment_id;
                        } 
                        else
                        {
                             echo "Shipment Not Created Because It's already created or something went wrong";
                        }


                        $ordert = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByAttribute('increment_id', $increment_id);
    
                        if ($ordert->canInvoice()) 
                        {
                            $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($ordert);
                            if (!$invoice->getTotalQty()) 
                            {
                                throw new \Magento\Framework\Exception\LocalizedException(__('You can\'t create an invoice without products.'));
            }

                            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                            $invoice->register();
                        
                            $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder());
                        
                            $transaction->save();

                            //$this->invoiceSender->send($invoice);

                            $ordert->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
                                   ->setIsCustomerNotified(true)
                                   ->save();
                }
   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableNamew = $resource->getTableName('sales_shipment'); 
 
        $sqlm = "Select `entity_id` FROM " . $tableNamew." Where `order_id` = '$orderID'";
        $trackdot = $connection->fetchRow($sqlm);  
        
        $lastid = $trackdot['entity_id'];

        $tableName1 = $resource->getTableName('sales_shipment_track'); 
        $sqlr = "Insert Into " . $tableName1 . " (`entity_id`,`parent_id`, `weight`, `qty`, `order_id`,`track_number`, `description`,`title`,`carrier_code`,`created_at`,`updated_at`) 
        Values ('','$lastid','','','$orderID','$docketnum1','','Dotzot Shipping Service','custom','$created_at','$updated_at')";
        $check = $connection->query($sqlr);
    
        if($uquery)
        {
            $this->messageManager->addSuccess(__('Tracking number has been generated Successfully   '.$docketnum1));
        }
        else
        {
            $this->messageManager->addError(__('Something went wrong!!!')); 
            //return $resultRedirect->setPath('sales/*/');
        } 
     
    }
         
      else
      {
         $this->messageManager->addError(__('Error Reason:     '.$resultdata['0']->Reason)); 
      }
     
 }  
        else
        {
             $errorReason = 'You cannot create more dotzot tracking number for this order';
             $this->messageManager->addError(__('Warning Reason:     '.$errorReason)); 
        }
 
    }

else
{
     $errorexcludeq = 'You cannot create mass / bulk dotzot tracking number, simply done one by one';
     $this->messageManager->addError(__('Warning Reason:     '.$errorexcludeq));
     $resultRedirect = $this->resultRedirectFactory->create();
}

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/*/');
        
    }
}