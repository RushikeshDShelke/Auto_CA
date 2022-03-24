<?php

namespace MagicToolbox\MagicZoomPlus\Controller\Adminhtml\Settings;

use MagicToolbox\MagicZoomPlus\Controller\Adminhtml\Settings;

class Index extends \MagicToolbox\MagicZoomPlus\Controller\Adminhtml\Settings
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('magiczoomplus/*/edit', ['active_tab' => $activeTab]);
        return $resultRedirect;
    }
}
