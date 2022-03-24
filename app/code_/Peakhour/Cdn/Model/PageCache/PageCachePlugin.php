<?php

namespace Peakhour\Cdn\Model\PageCache;

use Peakhour\Cdn\Helper\Data;
use Peakhour\Cdn\Model\Config;
use Magento\CacheInvalidate\Model\PurgeCache;


class PageCachePlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $helper;

    /**
     * PurgeCachePlugin constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config,
        Data $helper
    ) {
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     *
     * @param PurgeCache $subject
     * @param callable $proceed
     * @param array ...$args
     */
    public function aroundSendPurgeRequest(PurgeCache $subject, callable $proceed, ...$args) // @codingStandardsIgnoreLine - unused parameter
    {
        if ($this->config->isPeakhourEnabled() !== true) {
            $this->helper->debug("peakhour not enabled");
            $proceed(...$args);
        }
        else {
            $this->helper->debug("peakhour enabled !");
        }
    }
}
