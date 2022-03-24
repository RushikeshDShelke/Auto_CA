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

class Index extends \Delhivery\Lastmile\Controller\Adminhtml\Location
{
    /**
     * Manage Locations list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Delhivery_Lastmile::location');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage&#x20;Locations'));
        $resultPage->addBreadcrumb(__('Delhivery'), __('Delhivery'));
        $resultPage->addBreadcrumb(__('Manage&#x20;Locations'), __('Manage&#x20;Locations'));
        return $resultPage;
    }
}
