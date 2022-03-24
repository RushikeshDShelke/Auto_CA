<?php

namespace Wbcom\PincodeChecker\Block\Pincode;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * View constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_objectManager = $objectmanager;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->formKey = $formKey;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl(){
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl;
    }

    /**
     * @return string
     */
    public function getFormKey(){
        return $this->formKey->getFormKey();
    }

    public function getCurrentProduct() {
        return $this->registry->registry('current_product');
    }

    public function getColors(){
        $colors = [];
        $colors['form_back'] = $this->_scopeConfig->getValue('wbcompin/pincolor/header_back', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $colors['btn_color'] = $this->_scopeConfig->getValue('wbcompin/pincolor/btn_color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $colors['inner_color'] = $this->_scopeConfig->getValue('wbcompin/pincolor/inner_field_color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $colors;
    }
}
