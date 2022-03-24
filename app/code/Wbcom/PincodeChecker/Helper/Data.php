<?php

namespace Wbcom\PincodeChecker\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Data constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getModuleStatus(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $stausVal = $this->_scopeConfig->getValue("wbcompin/general/enable", $storeScope);
        return $stausVal;
    }

    /**
     * @return array
     */
    public function getMessages(){
        $message = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $message['not_available'] = $this->_scopeConfig->getValue("wbcompin/general/delivery_not", $storeScope);
        $message['available_not_cod'] = $this->_scopeConfig->getValue("wbcompin/general/delivery_possible", $storeScope);
        $message['available_cod'] = $this->_scopeConfig->getValue("wbcompin/general/delivery_possible_code", $storeScope);
        return $message;
    }

    /**
     * @return array
     */
    public function getPincodeFormColor(){
        $color = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $color['form_color'] = $this->_scopeConfig->getValue("wbcompin/pincolor/header_back", $storeScope);
        $color['btn_color'] = $this->_scopeConfig->getValue("wbcompin/pincolor/btn_color", $storeScope);
        return $color;
    }
}
