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
 
namespace Delhivery\Lastmile\Controller\Adminhtml\Pincode;
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
		$apiurl = $this->helper->getApiUrl('fetchPIN');
		$token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
		if($apiurl && $token)
		{
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$userModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Pincode');

			$path = $apiurl.'json/?token='.$token.'&pre-paid=Y';
			$retValue = $this->helper->Executecurl($path,'','');
			$codes = json_decode($retValue);
				if($codes)
				{
				
					$connection = $userModel->getResource()->getConnection();
					$tableName = $userModel->getResource()->getMainTable();
					$connection->truncateTable($tableName);
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						//$delete = Mage::getModel('lastmile/pincode')->deleteAll();
					foreach ($codes->delivery_codes as $item) {
					   try {
					   //$lastmilezip = Mage::getModel('lastmile/pincode')->loadByPin($item->postal_code->pin);
							$data = array();
							$data['district'] = $item->postal_code->district;
							$data['pin'] = $item->postal_code->pin;
							$data['pre_paid'] = ($item->postal_code->pre_paid=="Y")?1:0;
							$data['cash'] = ($item->postal_code->cash=="Y")?1:0;;
							$data['pickup'] = ($item->postal_code->pickup=="Y")?1:0;;
							$data['cod'] = ($item->postal_code->cod=="Y")?1:0;;
							$data['is_oda'] = ($item->postal_code->is_oda=="Y")?1:0;;
							$data['state_code'] = $item->postal_code->state_code;
							$userModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Pincode');
							$userModel->setData($data);
							$userModel->save();
					   } catch (Exception $e) {
						  $this->messageManager->addErrorMessage(__('Something went wrong')); 
					   }
					}
					$this->messageManager->addSuccessMessage(__('Pincode downloaded Successfully'));
				}else
				{
					$this->messageManager->addErrorMessage(__('Something went wrong'));
				}
		}
		else
		{
			$this->messageManager->addErrorMessage(__('Something went wrong'));
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('delhivery_lastmile/pincode');
		return $resultRedirect;
    }
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
