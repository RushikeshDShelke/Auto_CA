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

class Getawb extends \Magento\Backend\App\AbstractAction
{
	public function __construct(
		\Magento\Backend\App\Action\Context $context
		,\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	) {
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
	}
	
    public function execute()
    {
		
		$msg = '';
		$post = $this->getRequest();
		if (!$post->getParam('zipcode') && !$post->getParam('orderid')) {
			$msg = "PinCode is not serviceable by Delhivery.";
		}else
		{
			$zipcode = $post->getParam('zipcode');
			$orderid = $post->getParam('orderid');
			try{
				
				$objectManager1 = \Magento\Framework\App\ObjectManager::getInstance();
				$order = $objectManager1->create('\Magento\Sales\Model\Order')->load($orderid);
				$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
	
				$objectManager2 = \Magento\Framework\App\ObjectManager::getInstance();
				$userModel = $objectManager2->create('Delhivery\Lastmile\Model\Pincode');
				$userModel=$userModel->getCollection()->addFieldToFilter("pin",$zipcode)->getFirstItem();
				//echo "<pre>";
				//print_r($userModel->getData());
				if((!$userModel->getId()) || ( $userModel->getPrePaid() == 0) || (($payment_method_code == 'cashondelivery' or $payment_method_code == $this->getScopeConfig('delhivery_lastmile/general/cod_method')) && $userModel->getCod() == 0))
				{
					$msg = "Order PinCode is not serviceable by Delhivery.";
				}
				else
					{
						
						$objectManager3 = \Magento\Framework\App\ObjectManager::getInstance();
						$awbModel = $objectManager3->create('Delhivery\Lastmile\Model\Awb');
						$awbModel=$awbModel->getCollection()->addFieldToFilter("state",2)->getFirstItem()->setOrder("entity_id","asc");
						
						//echo "awb".$awbModel->getId();
						
						if(!$awbModel || $awbModel->getAwb() == '')
						{
							$msg = 'AWB number is not available. Please download more AWB';
						}
						else
						{
							$msg=$awbModel->getAwb();
							$objectManager4 = \Magento\Framework\App\ObjectManager::getInstance();
							$updateAwb = $objectManager4->create('Delhivery\Lastmile\Model\Awb')->load($awbModel->getId());
							$updateAwb->setState(3)->save();
						}
					}
			}
			catch (Exception $e) 
			{
				$msg = $e->getMessage();
			}
		}
		
		
		
		
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$data = array();
		$data[0]['awb'] = $msg;
		$output['resp'] = $data;
        $result = $this->resultJsonFactory->create();
		return $result->setData($output);
    }
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
