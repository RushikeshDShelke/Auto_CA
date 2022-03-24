<?php

namespace Kellton\Shipmentdelay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;
    protected $objectManager;

    const XML_PATH_MODULE_GENERAL = 'trans_email/ident_support/';

    public function __construct(Context $context, ObjectManagerInterface $objectManager, StoreManagerInterface $storeManager)
    {
        $this->objectManager = $objectManager;
        $this->storeManager  = $storeManager;
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {

        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getStoreId(){

        return $this->storeManager->getStore()->getId();
    }


    public function getModuleStatus($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_MODULE_GENERAL  . $code, $storeId);
    }

}