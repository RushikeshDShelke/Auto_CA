<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Supplier;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Products extends \Magento\Backend\App\Action
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
            'Cminds_Supplierfrontendproductuploader::supplier_products'
        );
        $resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        $resultPage->addBreadcrumb(
            __('Supplier Product List'),
            __('Supplier Product List')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Supplier Product List'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Cminds_Supplierfrontendproductuploader::supplier_products');
    }
}
