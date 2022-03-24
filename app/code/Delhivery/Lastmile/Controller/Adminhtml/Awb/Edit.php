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

class Edit extends \Delhivery\Lastmile\Controller\Adminhtml\Awb
{
    /**
     * Initialize current Manage AWB and set it in the registry.
     *
     * @return int
     */
    protected function initAwb()
    {
        $awbId = $this->getRequest()->getParam('awb_id');
        $this->coreRegistry->register(\Delhivery\Lastmile\Controller\RegistryConstants::CURRENT_AWB_ID, $awbId);

        return $awbId;
    }

    /**
     * Edit or create Manage AWB
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
		$packageId    = $this->getRequest()->getParam('awb_id');
		$awbModel = $this->_objectManager->create('Delhivery\Lastmile\Model\Awb')->load($packageId);
		//echo "<pre>";
		//print_r($awbModel->getData());die;
		
		$allowStatus=array('InTransit','Pending','Scheduled');
		if(!(in_array($awbModel->getStatus(),$allowStatus))){
			$this->messageManager->addErrorMessage(__('Sorry! You Can Edit (In Transit, Pending, Scheduled) Status packages only.'));
            $resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('delhivery_lastmile/awb');
			return $resultRedirect;
		}
        if ($packageId && !$awbModel->getAwbId()) {
           $this->messageManager->addErrorMessage(__('This lastmile package no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('delhivery_lastmile/awb');
			return $resultRedirect;
        }
		
		
        $awbId = $this->initAwb();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Delhivery_Lastmile::lastmile_awb');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage&#x20;AWBs'));
        $resultPage->addBreadcrumb(__('Delhivery'), __('Delhivery'));
        $resultPage->addBreadcrumb(__('Manage&#x20;AWBs'), __('Manage&#x20;AWBs'), $this->getUrl('delhivery_lastmile/awb'));

        if ($awbId === null) {
            $resultPage->addBreadcrumb(__('New Manage&#x20;AWB'), __('New Manage&#x20;AWB'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Manage&#x20;AWB'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Manage&#x20;AWB'), __('Edit Manage&#x20;AWB'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->awbRepository->getById($awbId)->getAwb()
            );
        }
        return $resultPage;
    }
}
