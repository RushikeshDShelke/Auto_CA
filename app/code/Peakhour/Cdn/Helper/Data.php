<?php

namespace Peakhour\Cdn\Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Peakhour\Cdn\Model\Config;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config $config,
     */
    private $config;


    /**
     * Data constructor.
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * @param $message
     */
    public function debug($message)
    {
        if ($this->config->isDebugEnabled()) {
            $this->_logger->debug($message);
        }
    }
}
