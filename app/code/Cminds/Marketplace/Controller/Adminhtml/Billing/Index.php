<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Billing;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Cminds_Marketplace::billing_reports';

    private $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
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
    private function initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cminds_Marketplace::billing_reports');
        $resultPage->addBreadcrumb(
            __('Marketplace Billing Report'),
            __('Marketplace Billing Report')
        );
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__('Marketplace Billing Report'));

        return $resultPage;
    }

    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(
                $this->_view->getLayout()
                    ->createBlock(
                        'Cminds\Marketplace\Block\Adminhtml'
                        . '\Billing\Billinglist\Grid'
                    )
                    ->toHtml()
            );
        } else {
            return $this->initAction();
        }
    }
}
