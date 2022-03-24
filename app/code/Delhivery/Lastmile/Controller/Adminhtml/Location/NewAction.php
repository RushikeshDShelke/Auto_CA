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
namespace Delhivery\Lastmile\Controller\Adminhtml\Location;

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
		$apiurl = $this->helper->getApiUrl('fetchLOC');
		$cl = $this->getScopeConfig('delhivery_lastmile/general/client_id');
		$token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
		if($apiurl && $token)
		{
			$url = $apiurl.'?token='.$token.'&cl='.urlencode($cl).'&limit=5000';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_TIMEOUT, 6000);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Token '.$token.''));
			$retValue = curl_exec($ch);
			if (curl_error($ch)) {
				$error_msg = curl_error($ch);
			}//echo "<pre>";
			$codes = json_decode($retValue);
			
			if($codes)
			{
				$codesData=$codes->data;
				if(sizeof($codesData))
				{
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$userModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Location');
						$connection = $userModel->getResource()->getConnection();
						$tableName = $userModel->getResource()->getMainTable();
						$connection->truncateTable($tableName);
						
					foreach ($codesData as $item) {
						try {
						   //$lastmilezip = Mage::getModel('lastmile/location')->loadByPin($item->pin);
								$data = array();
								$data['name'] = $item->name;
								$data['address'] = $item->address;
								$data['contact_person'] = $item->contact_person;
								$data['email'] = $item->email;
								$data['phone'] = $item->phone;
								$data['pin'] = $item->pin;
								$data['city'] = $item->city;
								$data['state'] = $item->state;
								$data['incoming_center'] = $item->incoming_center;
								$data['rto_center'] = $item->rto_center;
								$data['dto_center'] = $item->dto_center;
								$data['status'] = 1;
								$LocationModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Location');
								$LocationModel->setData($data);
								$LocationModel->save();
						}
						catch (Exception $e) {
						  $this->messageManager->addErrorMessage(__('Something went wrong')); 
					   }
					}
					$this->messageManager->addSuccessMessage(__('Location downloaded Successfully'));
				}else
				{
					$this->messageManager->addErrorMessage(__('Something went wrong')); 
				}
				
			}else
			{
				$this->messageManager->addErrorMessage(__('Something went wrong')); 
			}
			
			//print_r($codes);
		}else
		{
			$this->messageManager->addErrorMessage(__('Something went wrong')); 
		}
		
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('delhivery_lastmile/location');
		return $resultRedirect;
    }
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
