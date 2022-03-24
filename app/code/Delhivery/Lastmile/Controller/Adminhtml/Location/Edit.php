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

class Edit extends \Delhivery\Lastmile\Controller\Adminhtml\Location
{
    /**
     * Initialize current Manage Location and set it in the registry.
     *
     * @return int
     */
    protected function initLocation()
    {
        $locationId = $this->getRequest()->getParam('location_id');
        $this->coreRegistry->register(\Delhivery\Lastmile\Controller\RegistryConstants::CURRENT_LOCATION_ID, $locationId);

        return $locationId;
    }

    /**
     * Edit or create Manage Location
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $locationId = $this->initLocation();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Delhivery_Lastmile::lastmile_location');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage&#x20;Locations'));
        $resultPage->addBreadcrumb(__('Delhivery'), __('Delhivery'));
        $resultPage->addBreadcrumb(__('Manage&#x20;Locations'), __('Manage&#x20;Locations'), $this->getUrl('delhivery_lastmile/location'));

        if ($locationId === null) {
            $resultPage->addBreadcrumb(__('New Manage&#x20;Location'), __('New Manage&#x20;Location'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Manage&#x20;Location'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Manage&#x20;Location'), __('Edit Manage&#x20;Location'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->locationRepository->getById($locationId)->getName()
            );
        }
        return $resultPage;
    }
}
