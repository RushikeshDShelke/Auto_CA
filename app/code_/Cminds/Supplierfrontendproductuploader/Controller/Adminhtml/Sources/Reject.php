<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Adminhtml\Sources;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface as SourceModel;

class Reject extends Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        SourceModel $sourceModel,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->sourceModel = $sourceModel;
        $this->logger = $logger;
    }

    public function execute()
    {
        $sourceId = $this->_request->getParam('id');
        $sourceModel = $this->sourceModel->load($sourceId);

        if (!$sourceModel) {
            $this->messageManager->addErrorMessage(__('Source not found.'));
            return $this->_redirect("*/*/index");
        }

        if (SourceModel::STATUS_PENDING === (int) $sourceModel->getStatus()) {
            try {
                $sourceModel->setStatus(SourceModel::STATUS_REJECTED);
                $sourceModel->save();
                $this->messageManager->addSuccessMessage(
                    __('Suggested source was rejected.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Action failed.')
                );
                $this->logger->critical($e);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Source status is incompatible with selected action'));
        }

        return $this->_redirect("*/*/index");
    }

    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('Cminds_Supplierfrontendproductuploader::supplier_sources');
    }
}
