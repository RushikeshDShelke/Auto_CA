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

class Edit extends \Olegnax\BannerSlider\Controller\Adminhtml\Slides {
    const ADMIN_RESOURCE = 'Olegnax_BannerSlider::Slide_Edit';
	protected $resultPageFactory;

	public function __construct(
	\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context, $coreRegistry);
	}



	/**
	 * Edit action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute() {
		$id = $this->getRequest()->getParam('id');
		$model = $this->_objectManager->create(\Olegnax\BannerSlider\Model\Slides::class);

		if ($id) {
			$model->load($id);
			if (!$model->getId()) {
				$this->messageManager->addErrorMessage(__('This Slide no longer exists.'));
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/');
			}
		}

		$this->_coreRegistry->register('olegnax_bannerslider_slide', $model);

		$resultPage = $this->resultPageFactory->create();
		$this->initPage($resultPage)->addBreadcrumb(
				$id ? __('Edit Slide') : __('New Slide'),
				$id ? __('Edit Slide') : __('New Slide')
		);
		$resultPage->getConfig()->getTitle()->prepend(__('Slide'));
		$resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Slide %1',
								$model->getId()) : __('New Slide') );
		return $resultPage;
	}

}
