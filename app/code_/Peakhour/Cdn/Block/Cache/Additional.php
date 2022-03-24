<?php

namespace Peakhour\Cdn\Block\Cache;

use Peakhour\Cdn\Model\Config;

class Additional extends \Magento\Backend\Block\Template
{

    /**
     * @var Config
     */
    private $config;

    /**
     * Additional constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;

        parent::__construct($context, $data);
    }

    /**
     * Check if block can be displayed
     *
     * @return bool
     */
    public function canShowBlock()
    {
        if ($this->config->getType() == Config::PEAKHOUR && $this->config->isEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFlushWildcardUrl()
    {
//        return $this->getUrl('adminhtml/cdn/flushWildcard');
        return $this->getUrl('*/cdn/flushWildcard');
    }


    /**
     * @return string
     */
    public function getFlushAllUrl()
    {
        return $this->getUrl('adminhtml/cdn/flushAll');
    }


    /**
     * @return array
     */
    public function getStoreOptions()
    {
        return $this->_storeManager->getStores();
    }


}
