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

class Delete extends \Delhivery\Lastmile\Controller\Adminhtml\Location
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('location_id');
        if ($id) {
            try {
                $this->locationRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The Manage&#x20;Location has been deleted.'));
                $resultRedirect->setPath('delhivery_lastmile/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The Manage&#x20;Location no longer exists.'));
                return $resultRedirect->setPath('delhivery_lastmile/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('delhivery_lastmile/location/edit', ['location_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the Manage&#x20;Location'));
                return $resultRedirect->setPath('delhivery_lastmile/location/edit', ['location_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Manage&#x20;Location to delete.'));
        $resultRedirect->setPath('delhivery_lastmile/*/');
        return $resultRedirect;
    }
}
