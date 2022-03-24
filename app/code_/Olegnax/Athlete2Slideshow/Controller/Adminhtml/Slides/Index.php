<?php

namespace Olegnax\Athlete2Slideshow\Controller\Adminhtml\Slides;

class Index extends \Magento\Backend\App\Action {

    const ADMIN_RESOURCE = 'Olegnax_Athlete2Slideshow::Slides_Index';

    protected $_pageFactory;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory) {
	$this->_pageFactory = $pageFactory;
	return parent::__construct($context);
    }

    public function execute() {
	$resultPage = $this->_pageFactory->create();
	$resultPage->getConfig()->getTitle()->prepend((__('Athlete Slide Manager')));

	return $resultPage;
    }

}
