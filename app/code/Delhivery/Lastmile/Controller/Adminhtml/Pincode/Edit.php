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

class Edit extends \Delhivery\Lastmile\Controller\Adminhtml\Pincode
{
    /**
     * Initialize current Manage Pincode and set it in the registry.
     *
     * @return int
     */
    protected function initPincode()
    {
        $pincodeId = $this->getRequest()->getParam('pincode_id');
        $this->coreRegistry->register(\Delhivery\Lastmile\Controller\RegistryConstants::CURRENT_PINCODE_ID, $pincodeId);

        return $pincodeId;
    }

    /**
     * Edit or create Manage Pincode
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $pincodeId = $this->initPincode();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Delhivery_Lastmile::lastmile_pincode');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage&#x20;Pincodes'));
        $resultPage->addBreadcrumb(__('Delhivery'), __('Delhivery'));
        $resultPage->addBreadcrumb(__('Manage&#x20;Pincodes'), __('Manage&#x20;Pincodes'), $this->getUrl('delhivery_lastmile/pincode'));

        if ($pincodeId === null) {
            $resultPage->addBreadcrumb(__('New Manage&#x20;Pincode'), __('New Manage&#x20;Pincode'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Manage&#x20;Pincode'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Manage&#x20;Pincode'), __('Edit Manage&#x20;Pincode'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->pincodeRepository->getById($pincodeId)->getDistrict()
            );
        }
        return $resultPage;
    }
}
