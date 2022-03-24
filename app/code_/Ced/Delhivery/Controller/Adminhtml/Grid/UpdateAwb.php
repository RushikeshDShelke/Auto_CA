<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Delhivery\Controller\Adminhtml\Grid;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class UpdateAwb extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
 
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope 
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scope=$scope;
    }
 
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {

$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('hello');
 
        $model = $this->_objectManager->create('Ced\Delhivery\Model\Awb'); 
        $apiurl =  $this->scope->getValue('carriers/delhivery/gateway_url');
        $token = $this->scope->getValue('carriers/delhivery/license_key');		
        
        if($apiurl && $token)
        {
        $waybills = $model->getCollection()->addFieldToFilter('status','InTransit');
        if(sizeof($waybills)){ //No update to perform if count is zero
          $awbs = '';
          foreach($waybills as $waybill){
               $awbs .= $waybill['awb'].',';  
          }
          $path = $apiurl.'api/packages/json/?verbose=0&token='.$token.'&waybill='.$awbs; 
          $retValue =$this->_objectManager->create('Ced\Delhivery\Helper\Data')->Executecurl($path,'','');
          
          $statusupdates = json_decode($retValue);
          
        //  if($statusupdates['Error'])
        //  {
    	//		  $this->messageManager->addErrorMessage( __('No such waybill or Order Id found') );
    	//			$this->_redirect('delhivery/grid/awb');
        //  }
          foreach ($statusupdates->ShipmentData as $item) {
             $model =$this->_objectManager->create('Ced\Delhivery\Model\Awb')->load($item->Shipment->AWB,'awb');
             $data = array();
             $data['awb'] = $item->Shipment->AWB;  
             $data['status'] = $item->Shipment->Status->Status;
             $model->addData($data);
             $model->save();
          }
      }
     $this->messageManager->addSuccessMessage( __('Waybill(s) Updated Successfully') );
    }
    else
    {
       $this->messageManager->addErrorMessage( __('Please add valid License Key and Gateway URL in plugin configuration') );
    }    
    $this->_redirect('delhivery/grid/awb');
   }

}
