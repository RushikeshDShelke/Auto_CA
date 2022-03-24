<?php
/**
 * Set layout for blog pages
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper;

class ChangeBlogLayout implements ObserverInterface
{

    protected $config;
    protected $scopeConfig;

    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $variable = $this->getConfig('athlete2_settings/blog/blog_list_page_layout');
        if ($this->getConfig(Helper::XML_ENABLED) && !empty($variable)) {
            $this->config->setPageLayout($variable);
        }
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

}
