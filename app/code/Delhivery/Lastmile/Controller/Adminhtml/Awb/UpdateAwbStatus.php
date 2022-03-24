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

class UpdateAwbStatus extends \Magento\Backend\App\Action
{
    /**
     * Redirect result factory
     * 
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;
	protected $helper;
    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
		\Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
		\Magento\Ui\Component\MassAction\Filter $filter,
		\Delhivery\Lastmile\Helper\Data $helper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
		$this->helper = $helper;
		$this->filter            = $filter;
		$this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
		$awbArray=array();
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		foreach ($collection as $awbModel) {
					$awbArray[]=$awbModel->getId();
				}
		$apiurl = $this->helper->getApiUrl('syncAWB');
		$token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
		$post_data=$this->getRequest()->getParams();
		if($apiurl && $token && $awbArray)
		{
			$awbs="";
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$userModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb');
			$userModel=$userModel->getCollection()->addFieldToFilter('awb_id', array('in' => $awbArray))
								->addFieldToFilter('status', array('in' => array('InTransit','Dispatched','Pending','NotPicked','Manifested')));
			if(count($userModel)){					
				$awbs = '';
				foreach($userModel as $waybill){
					if($waybill->getAwb()){
						if($waybill->getState() == 1)
						{
					   		$awbs .= $waybill->getAwb().',';
						}
					}
				}
				if($awbs)
				{
					try{
						$path = $apiurl.'json/?verbose=0&token='.$token.'&waybill='.$awbs;
						$retValue = $this->helper->Executecurl($path,'','');
						$statusupdates = json_decode($retValue);
						if($statusupdates)
						{
							$objArray=get_object_vars($statusupdates);
						}else
						{
							$objArray=array();
						}
						if(array_key_exists("ShipmentData",$objArray) && $objArray['ShipmentData'])
						{
							foreach ($statusupdates->ShipmentData as $item) {			   		   
								$lmawb = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb')
										->getCollection()->addFieldToFilter('awb',$item->Shipment->AWB)->getFirstItem();
								if($lmawb->getAwbId())
								{
								   $model = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb')->load($lmawb->getAwbId());
								   $model->setStatus(preg_replace('/\s+/', '', $item->Shipment->Status->Status));
								   $model->setStatusType($item->Shipment->Status->StatusType)->save();
								}
							}
							$this->messageManager->addSuccessMessage(__(count($collection).' Waybill(s) Updated Successfully'));
						}else
						{
							
								$this->messageManager->addErrorMessage(__('Something went wrong. please connect to support.')); 
								$resultRedirect = $this->resultRedirectFactory->create();
								$resultRedirect->setPath('delhivery_lastmile/awb');
								return $resultRedirect;
						}
					}catch(Exception $e)
					{
						$this->messageManager->addErrorMessage(__('Something went wrong. please connect to support.'));
						$resultRedirect = $this->resultRedirectFactory->create();
						$resultRedirect->setPath('delhivery_lastmile/awb');
						return $resultRedirect;
					}
				}
				else
				{
					
						$this->messageManager->addErrorMessage(__('0 AWBs are updated')); 
						$resultRedirect = $this->resultRedirectFactory->create();
						$resultRedirect->setPath('delhivery_lastmile/awb');
						return $resultRedirect;
				}
				
			}else
			{
				
					$this->messageManager->addErrorMessage(__('0 AWBs are updated')); 
					$resultRedirect = $this->resultRedirectFactory->create();
					$resultRedirect->setPath('delhivery_lastmile/awb');
					return $resultRedirect;
			}
		}else
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
