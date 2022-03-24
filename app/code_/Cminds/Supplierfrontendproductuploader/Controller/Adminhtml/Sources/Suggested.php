<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Sources;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Suggested extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Cminds_Supplierfrontendproductuploader::supplier_sources'
        );
        $resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        $resultPage->addBreadcrumb(
            __('Supplier Suggested Sources List'),
            __('Supplier Suggested Sources List')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Supplier Suggested Sources List'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Cminds_Supplierfrontendproductuploader::supplier_sources');
    }
}
