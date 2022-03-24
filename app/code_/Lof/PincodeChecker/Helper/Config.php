<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\PincodeChecker\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Get pincode status message
     */
    public function getMessage($status, $pincode)
    {
        if($status){
            $message = "<h3>".$this->getSuccessMessage()."</h3>";
        }else{
            $message = "<h3 style='color:red'>".$this->getFailMessage()."</h3>";
        }

        return $message;
    }

    /**
     * Check module enable
     */
    public function getIsEnable()
    {
        return $this->scopeConfig->getValue('pincode/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get check on addtocart config value
     */
    public function getIsCheckonAddtoCart()
    {
        return $this->scopeConfig->getValue('pincode/general/checkaddtocart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get success message config value
     */
    public function getSuccessMessage()
    {
        return $this->scopeConfig->getValue('pincode/general/successmessage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get fail message config value
     */
    public function getFailMessage()
    {
        return $this->scopeConfig->getValue('pincode/general/failmessage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}