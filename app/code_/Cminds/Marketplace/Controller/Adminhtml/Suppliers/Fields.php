<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Suppliers;

use Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Supplier\Supplierlist;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Fields extends Action
{
    protected $resultPageFactory;
    protected $supplierlist;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Supplierlist $supplierlist
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->supplierlist = $supplierlist;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cminds_Marketplace::profile_fields');
        $resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        $resultPage->addBreadcrumb(
            __('Marketplace Profile Fields'),
            __('Marketplace Profile Fields')
        );
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__('Marketplace Profile Fields'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Cminds_Marketplace::profile_fields');
    }
}
