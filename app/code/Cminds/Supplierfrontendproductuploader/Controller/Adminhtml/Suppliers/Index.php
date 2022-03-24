<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Suppliers;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session.
     */
    const ADMIN_RESOURCE = 'Cminds_Supplierfrontendproductuploader::manage_suppliers';

    /**
     * Page factory object.
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Object constructor.
     *
     * @param Context     $context           Context object.
     * @param PageFactory $resultPageFactory Page factory object.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initAction()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Cminds_Supplierfrontendproductuploader::manage_suppliers');

        $resultPage->addBreadcrumb(
            __('Manage Suppliers'),
            __('Manage Suppliers')
        );

        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__('Manage Suppliers'));

        return $resultPage;
    }

    /**
     * Execute controller main logic.
     *
     * @return \Magento\Backend\Model\View\Result\Page|string
     */
    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(
                $this->_view->getLayout()
                    ->createBlock(
                        'Cminds\Supplierfrontendproductuploader\Block\Adminhtml'
                        . '\Supplier\Supplierlist\Grid'
                    )
                    ->toHtml()
            );
        } else {
            return $this->initAction();
        }
    }
}
