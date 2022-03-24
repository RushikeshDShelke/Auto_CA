<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\PincodeChecker\Controller\Adminhtml\Pincodechecker;

class Delete extends \Lof\PincodeChecker\Controller\Adminhtml\Pincodechecker
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('pincode_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Lof\PincodeChecker\Model\Pincodechecker');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('You deleted the Pincode.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['pincode_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a Pincode to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
