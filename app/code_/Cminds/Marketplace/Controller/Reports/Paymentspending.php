<?php

namespace Cminds\Marketplace\Controller\Reports;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Paymentspending extends AbstractController
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $helper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );
    }

    public function execute()
    {


        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $this->_view->loadLayout();
        
        $this->renderBlocks();
        $this->_view->renderLayout();
    }
}
