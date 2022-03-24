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
class CancelPackage extends \Magento\Backend\App\Action
{
	 protected $resultPageFactory;
	 protected $helper;
	 
	public function __construct(
		Data $helper,
		Context $context,
		PageFactory $resultPageFactory,
		\Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $collectionFactory,
		\Magento\Ui\Component\MassAction\Filter $filter,
		\Magento\Theme\Block\Html\Header\Logo $logo
	){
		$this->_logo = $logo;
		$this->resultPageFactory = $resultPageFactory;
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
		//echo "<pre>";
		//print_r($this->getRequest()->getParams());
		$apicancelurl = $this->helper->getApiUrl('cancelAWB');
		$token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
		if($apicancelurl && $token)
		{
				$lastmilesIds=$this->filter->getCollection($this->collectionFactory->create());	
				$successWaybills=0;
				$unsuccessWaybil=0;
				$errMsg='';
				$msg='';
				foreach($lastmilesIds as $loadLastmileRec){
					//$loadLastmileRec = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb')->load($lastmilesid);
					$allowStatus=array('InTransit','Pending','Open','Scheduled');
					if(in_array($loadLastmileRec->getStatus(),$allowStatus))
					{
						$url = $apicancelurl;//"http://test.delhivery.com/api/p/edit";
						$postData=array('waybill'=>$loadLastmileRec->getAwb(),'cancellation'=>true);
						$curl_responsee=$this->helper->cancelPackageExecuteUrl($url,$postData,$token);
						$curl_response = json_decode($curl_responsee);
						try{
							if($curl_response->status == 1){
								$loadLastmileRec->setStatus('Cancelled');
								$loadLastmileRec->save();
								$this->messageManager->addSuccessMessage(__("#".$loadLastmileRec->getAwb()." Cancellation Successfully."));
							}else{
								$this->messageManager->addErrorMessage(__("! #".$loadLastmileRec->getAwb()." Failed to Submit Cancellation"));
							}
						}catch(Exception $e)
						{
							$this->messageManager->addErrorMessage(__('Something went wrong. please connect to support.'));
							$resultRedirect = $this->resultRedirectFactory->create();
							$resultRedirect->setPath('delhivery_lastmile/awb');
							return $resultRedirect;
						}
					}else{
						//$unsuccessWaybil++;
						$this->messageManager->addErrorMessage(__('#'.$loadLastmileRec->getAwb().' Sorry! You Can Cancel (In Transit, Pending, Open, Scheduled) Status packages only.'));
					}
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
