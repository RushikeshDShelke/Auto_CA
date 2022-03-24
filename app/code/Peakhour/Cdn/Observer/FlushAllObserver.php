<?php

namespace Peakhour\Cdn\Observer;

use Peakhour\Cdn\Model\Config;
use Peakhour\Cdn\Model\Api;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class FlushAllObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /*
    * @param Config $config
    * @param PurgeCache $purgeCache
    * @param CacheTags $cacheTags
    */
    public function __construct(
       Config $config,
       Api $api,
       LoggerInterface $logger
    ) {
       $this->config = $config;
       $this->api = $api;
       $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == Config::PEAKHOUR && $this->config->isEnabled()) {
//            $event = $observer->getEvent();
            $this->api->purgeAll(API::FLUSH_CDN);
        }
    }
}