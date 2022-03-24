<?php

namespace Cminds\Marketplace\Controller\Settings;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;

class Profile extends AbstractController
{
    private $customerSession;
    private $registry;

    public function __construct(
        Context $context,
        Data $helper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Registry $registry
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->customerSession = $customerSession;
        $this->registry = $registry;
    }

    public function execute()
    {
        $customer = $this->customerSession->getCustomer();
        $this->registry->register('customer', $customer);

        return parent::execute();
    }
}
