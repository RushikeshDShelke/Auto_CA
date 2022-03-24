<?php

namespace Cminds\Marketplace\Controller\Shipment;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Create extends AbstractController
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $helper,
        Registry $registry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->registry = $registry;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $this->_view->loadLayout();

        $id = $this->getRequest()->getParam('id');
        $this->registry->register('order_id', $id);

        $this->renderBlocks();

        $this->_view->renderLayout();
    }
}
