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

class NewAction extends \Magento\Backend\App\Action
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
		\Delhivery\Lastmile\Helper\Data $helper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
		$this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $apiurl = $this->helper->getApiUrl('fetchAWB');
		$cl = $this->getScopeConfig('delhivery_lastmile/general/client_id');
		$token = $this->getScopeConfig('delhivery_lastmile/general/license_key');
		if($apiurl && $token)
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$userModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb');
			$userModel=$userModel->getCollection()->addFieldToFilter("state",2);
			if(count($userModel) > 100){
				$this->messageManager->addErrorMessage(__('More than 100 AWB still available to use.'));
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('delhivery_lastmile/awb');
				return $resultRedirect;
			}
			
			$path = $apiurl.'json/?token='.$token.'&count=100&cl='.urlencode($cl);
			$retValue = $this->helper->Executecurl($path,'','');
			$codes = json_decode($retValue);
			if($codes)
			{
				$awbs = explode(',',$codes);
				
				foreach ($awbs as $awb){	
					 try {	   
						$data['awb'] = $awb;
						$data['state'] = 2;
						$userModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb');
						$userModel->setData($data);
						$userModel->save(); 
					 }catch (Exception $e) {
						  $this->messageManager->addErrorMessage(__('Something went wrong')); 
					 }
				}
				
			$this->messageManager->addSuccessMessage(__('AWB downloaded Successfully'));
			}else
			{
				$this->messageManager->addErrorMessage(__('Something went wrong'));
			}
		}else
		{
			$this->messageManager->addErrorMessage(__('Something went wrong'));
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
