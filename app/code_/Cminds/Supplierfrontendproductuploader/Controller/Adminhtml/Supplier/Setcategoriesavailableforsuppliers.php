<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Supplier;

use Cminds\Supplierfrontendproductuploader\Model\Service\CategoryService;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Setcategoriesavailableforsuppliers extends Action
{
    private $categoryService;

    public function __construct(
        Context $context,
        CategoryService $categoryService
    ) {
        parent::__construct($context);

        $this->categoryService = $categoryService;
    }

    public function execute()
    {
        $this->categoryService->setCategoriesAvailability();
        $this->messageManager->addSuccessMessage(
            __('All categories has been set as available.')
        );
        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
