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


class Trackcancel extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::trackcancel';

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
            
                    $sql = "Select * FROM " . $tableName." Where `entity_id` = '$orderID'";
                    $resultdot = $connection->fetchRow($sql);
                    
                    $docketno = $resultdot['docketno'];
        
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $conf = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/demo_enable_mode');
                    $check_valid = $resultdot['dotzotrack_status']; 
        
                        if($check_valid == 'success')
                        {
                            if($conf == '1')
                            {

                                    $fieldsdata = array (
                                                            'DocketNo' => $docketno,
                                                        );
                                 $headers = array('Content-Type: application/json');
                                 $ch = curl_init();
                         	 	 curl_setopt($ch,CURLOPT_URL,'http://instacom-staging.azurewebsites.net/RestService/DocketTrackingService.svc/GetDocketTrackingDetails');
                        		 curl_setopt($ch,CURLOPT_POST,true);
                                 curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
                                 curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                                 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
                                 curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($fieldsdata));
                                 $result = curl_exec($ch);
                                 curl_close($ch);     
                                 $resultdata = json_decode($result,true);

                                 $pre_type = array();

                                foreach($resultdata as $results)
                                {
			                            foreach($results['Detail'] as $finalresult)
                                        {
				                            $pre_type[] = $finalresult['CURRENT_STATUS'];
			                            } 
                                }

		                            if(in_array('Picked up and Booking processed', $pre_type))
		                            {
   			                            $prevent_type = 'R';
		                            }
		                            else
		                            {
  			                            $prevent_type = 'P';
		                            }
		
		$clientid = 'DOTZOT';
        $password = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/dotzotdemo_password');
        $dock =     $docketno;
        $user =     $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/dotzotdemo_username');

        $fields = '<PreventDocketMain xmlns="http://schemas.datacontract.org/2004/07/WebX.Entity"> 
                        <ClientId>'.$clientid.'</ClientId>
                        <PassWord>'.$password.'</PassWord>
                        <PreventDocketList>
                         <PreventDocket> 
                            <AddRemove>Add</AddRemove>
                            <DockNo>'.$dock.'</DockNo> 
                            </PreventDocket> 
                        </PreventDocketList>
                        <Type>'.$prevent_type.'</Type>
                        <UserId>'.$user.'</UserId>
                        </PreventDocketMain>';
                        
         $headers = array('Content-Type: application/xml');
         $ch = curl_init();
    	 curl_setopt($ch,CURLOPT_URL,'http://instacom-staging.azurewebsites.net/RestService/PreventOrderDataService.svc/PreventOrderData');         
		 curl_setopt($ch,CURLOPT_POST,true);
         curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
         $result = curl_exec($ch);
         curl_close($ch);
        
         $resultdataw = simplexml_load_string($result);
         
          if($resultdataw->Success == 'true')
	    {
            $sqlq = "Update " . $tableName . " Set dotzotrack_status = 'cancel' Where entity_id = $orderID";
            $uquery = $connection->query($sqlq);
            $sd = $resultdataw->ErrorMessage;
            $this->messageManager->addSuccess(__('Tracking number has been Canceled'));
    	}
 
         if($resultdataw->Success == 'false')
		{
          $error_msg = $resultdataw->ErrorMessage;
          $this->messageManager->addError(__('Error Status  '.$error_msg));
          $resultRedirect = $this->resultRedirectFactory->create();
          return $resultRedirect->setPath('sales/order/index');
		}
        }
        else
        {
           $fieldsdatar = array (
                            'DocketNo' => $docketno,
                          );
         $headers = array('Content-Type: application/json');
         $ch = curl_init();
 	 	 curl_setopt($ch,CURLOPT_URL,'https://instacom.dotzot.in/RestService/DocketTrackingService.svc/GetDocketTrackingDetails');
		 curl_setopt($ch,CURLOPT_POST,true);
         curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($fieldsdatar));
         $result = curl_exec($ch);
         curl_close($ch);     
         $resultdatar = json_decode($result,true);
         $pre_type = array();
         
        foreach($resultdatar as $results)
        {
			foreach($results['Detail'] as $finalresult)
            {
				$pre_type[] = $finalresult['CURRENT_STATUS'];
			} 
        }

		if(in_array('Picked up and Booking processed', $pre_type))
		{
   			$prevent_type = 'R';
		}
		else
		{
  			$prevent_type = 'P';
		}
		
		$clientid = 'INSTACOM';
        $password = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/dotzotlive_password');
        $dock =     $docketno;
        $user =     $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('prshippingtracking/services/shippingmodule/dotzotlive_username');
   
        $fieldsr = '<PreventDocketMain xmlns="http://schemas.datacontract.org/2004/07/WebX.Entity"> 
                        <ClientId>'.$clientid.'</ClientId>
                        <PassWord>'.$password.'</PassWord>
                        <PreventDocketList>
                         <PreventDocket> 
                            <AddRemove>Add</AddRemove>
                            <DockNo>'.$dock.'</DockNo> 
                            </PreventDocket> 
                        </PreventDocketList>
                        <Type>'.$prevent_type.'</Type>
                        <UserId>'.$user.'</UserId>
                        </PreventDocketMain>';
                
         $headers = array('Content-Type: application/xml');
         $ch = curl_init();
    	 curl_setopt($ch,CURLOPT_URL,'https://instacom.dotzot.in/RestService/PreventOrderDataService.svc/PreventOrderData');          
		 curl_setopt($ch,CURLOPT_POST,true);
         curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldsr);
         $result1 = curl_exec($ch);
         curl_close($ch);
         $resultdatao = simplexml_load_string($result1); 
         
        
         if($resultdatao->Success == 'true')
	    {
            $sqlq = "Update " . $tableName . " Set dotzotrack_status = 'cancel' Where entity_id = $orderID";
            $uquery = $connection->query($sqlq);
            $sd = $resultdatao->ErrorMessage;
            $this->messageManager->addSuccess(__('Tracking number has been Canceled'));
    	}
 
         if($resultdatao->Success == 'false')
		{
          $error_msg = $resultdatao->ErrorMessage;
          $this->messageManager->addError(__('Error Status  '.$error_msg));
          $resultRedirect = $this->resultRedirectFactory->create();
          return $resultRedirect->setPath('sales/order/index');
		}
		
        }
		


        }
        else
        {
            $errorReason = 'Invalid Action';
            $this->messageManager->addError(__('Warning Reason:     '.$errorReason)); 
        }
    }
    else
    {
            $errorexcludeq = 'You cannot create mass / bulk action on this order';
            $this->messageManager->addError(__('Warning Reason:     '.$errorexcludeq));
            $resultRedirect = $this->resultRedirectFactory->create();
    }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/index');
    }
}