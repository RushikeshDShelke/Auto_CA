<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Sources;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
      * @var \Magento\Framework\Registry
      */
    protected $_registry;

    /**
     * Object constructor.
     *
     * @param Context                       $context           Context object.
     * @param PageFactory                   $resultPageFactory Page factory object.
     * @param SourcesRepositoryInterface    $sourcesRepository Sources Repository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->_registry = $registry;
    }

    public function execute()
    {
        $sourceId = $this->_request->getParam('id');

        $sourceModel = $this->_objectManager->create(\Cminds\Supplierfrontendproductuploader\Model\Sources::class);

        if ($sourceId) {
            $sourceModel->load($sourceId);
            if (!$sourceModel->getId()) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_registry->register('source_item', $sourceModel);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(__('Suggested Source'), __('Suggested Source'));
        $resultPage->getConfig()->getTitle()->prepend(__('Supplier Suggested Source'));

        return $resultPage;
    }

    /**
     * check authorization.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Cminds_Supplierfrontendproductuploader::supplier_sources');
    }

    /**
     * Init actions.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cminds_Supplierfrontendproductuploader::supplier_sources')
            ->addBreadcrumb(__('Catalog'), __('Catalog'))
            ->addBreadcrumb(__('Supplier Suggested Source'), __('Supplier Suggested Source'));
        return $resultPage;
    }
}
