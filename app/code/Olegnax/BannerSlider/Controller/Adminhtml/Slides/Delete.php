<?php

/**
 * Olegnax BannerSlider
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_BannerSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\BannerSlider\Controller\Adminhtml\Slides;

class Delete extends \Olegnax\BannerSlider\Controller\Adminhtml\Slides {
    const ADMIN_RESOURCE = 'Olegnax_BannerSlider::Slide_Delete';
	/**
	 * Delete action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute() {
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		// check if we know what should be deleted
		$id = $this->getRequest()->getParam('id');
		if ($id) {
			try {
				// init model and delete
				$model = $this->_objectManager->create(\Olegnax\BannerSlider\Model\Slides::class);
				$model->load($id);
				$model->delete();
				// display success message
				$this->messageManager->addSuccessMessage(__('Slide has been deleted.'));
				$this->messageManager->addWarningMessage(__('Banner slide has been changed. Please go to Cache Management and Flush Magento Cache.'));
				// go to grid
				return $resultRedirect->setPath('*/*/');
			} catch (\Exception $e) {
				// display error message
				$this->messageManager->addErrorMessage($e->getMessage());
				// go back to edit form
				return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
			}
		}
		// display error message
		$this->messageManager->addErrorMessage(__('We can\'t find a Slide to delete.'));
		// go to grid
		return $resultRedirect->setPath('*/*/');
	}

}
