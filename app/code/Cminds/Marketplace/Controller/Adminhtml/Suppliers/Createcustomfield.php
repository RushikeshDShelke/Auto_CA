<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Suppliers;

use Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Supplier\Supplierlist;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Createcustomfield extends Action
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
        $this->_forward('editcustomfield');
    }
}
