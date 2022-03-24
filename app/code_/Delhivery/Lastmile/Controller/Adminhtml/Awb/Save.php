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
use Delhivery\Lastmile\Helper\Data;
class Save extends \Delhivery\Lastmile\Controller\Adminhtml\Awb
{
    /**
     * Manage AWB factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\AwbInterfaceFactory
     */
    protected $awbFactory;
	protected $helper;
    /**
     * Data Object Processor
     * 
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data Object Helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;


    /**
     * Data Persistor
     * 
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Delhivery\Lastmile\Api\AwbRepositoryInterface $awbRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Delhivery\Lastmile\Api\Data\AwbInterfaceFactory $awbFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Delhivery\Lastmile\Model\UploaderPool $uploaderPool
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
		Data $helper,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Delhivery\Lastmile\Api\AwbRepositoryInterface $awbRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Delhivery\Lastmile\Api\Data\AwbInterfaceFactory $awbFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
		$this->helper = $helper;
        $this->awbFactory          = $awbFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper    = $dataObjectHelper;
        $this->dataPersistor       = $dataPersistor;
        parent::__construct($context, $coreRegistry, $awbRepository, $resultPageFactory);
    }

    /**
     * run the action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Delhivery\Lastmile\Api\Data\AwbInterface $awb */
		$apiediturl = $this->helper->getApiUrl('editsaveAWB');
		$token = $this->getScopeConfig('delhivery_lastmile/general/license_key');
		if($apiediturl && $token){
			if ($data = $this->getRequest()->getPostValue()) {
				try {
					$datas='{"waybill":"'.$data["awb"].'",
								"phone":"'.$data["phone"].'",
								"name":"'.$data["shipment_to"].'",
								"add":"'.$data["address"].'",
								"shipment_length":'.number_format((float)$data["shipment_length"],1).',
								"shipment_width":'.number_format((float)$data["shipment_width"],1).',
								"shipment_height":'.number_format((float)$data["shipment_height"],1).',
								"gm":'.number_format((float)$data["weight"],1).'}';
								
					$url = $apiediturl;
					$curl_responsee=$this->helper->saveUpdateCurl($url,$datas,$token);
					$curl_response = json_decode($curl_responsee);
					if($curl_response->status==1 or $curl_response->status==true){
						$this->messageManager->addSuccessMessage(__(' #'.$curl_response->waybill.' Waybill(s) Package Update Successfully.'));
					}else{
						$this->messageManager->addErrorMessage(__($curl_response->error));
					}
					$resultRedirect = $this->resultRedirectFactory->create();
					$resultRedirect->setPath('delhivery_lastmile/awb');
					return $resultRedirect;
				}catch (Exception $e) {
					$this->messageManager->addErrorMessage(__("Something Went Wrong."));
					$resultRedirect = $this->resultRedirectFactory->create();
					$resultRedirect->setPath('delhivery_lastmile/awb');
					return $resultRedirect;
				}
			}
		}else{
			$this->messageManager->addErrorMessage(__('Please add valid License Key and Gateway URL in plugin configuration'));
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('delhivery_lastmile/awb');
			return $resultRedirect;
		}
    }
	
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }

    /**
     * @param string $type
     * @return \Delhivery\Lastmile\Model\Uploader
     * @throws \Exception
     */
}
