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

class Delete extends \Delhivery\Lastmile\Controller\Adminhtml\Pincode
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('pincode_id');
        if ($id) {
            try {
                $this->pincodeRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The Manage&#x20;Pincode has been deleted.'));
                $resultRedirect->setPath('delhivery_lastmile/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The Manage&#x20;Pincode no longer exists.'));
                return $resultRedirect->setPath('delhivery_lastmile/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('delhivery_lastmile/pincode/edit', ['pincode_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the Manage&#x20;Pincode'));
                return $resultRedirect->setPath('delhivery_lastmile/pincode/edit', ['pincode_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Manage&#x20;Pincode to delete.'));
        $resultRedirect->setPath('delhivery_lastmile/*/');
        return $resultRedirect;
    }
}
