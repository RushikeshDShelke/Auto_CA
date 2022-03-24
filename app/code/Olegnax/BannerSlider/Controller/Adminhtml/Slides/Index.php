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

class Index extends \Magento\Backend\App\Action {

	const ADMIN_RESOURCE = 'Olegnax_BannerSlider::Slide_Index';

	protected $_pageFactory;

	public function __construct(
	\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory) {
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute() {
		$resultPage = $this->_pageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend((__('Banner Slide Manager')));

		return $resultPage;
	}

}
