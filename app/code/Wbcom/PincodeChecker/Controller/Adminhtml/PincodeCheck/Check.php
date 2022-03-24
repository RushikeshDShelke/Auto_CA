<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\PincodeCheck;

class Check extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Pincode Availability Check List'));
        $resultPage->setActiveMenu('Wbcom_PincodeChecker::check_list');
        $resultPage->addBreadcrumb(__('Pincode Check List'), __('Pincode Check List'));
        return $resultPage;
    }
}
