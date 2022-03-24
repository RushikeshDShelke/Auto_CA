<?php

namespace Peakhour\Cdn\Observer;


use Peakhour\Cdn\Model\Config;
use Peakhour\Cdn\Model\Api;
use Magento\Framework\Event\ObserverInterface;
use Peakhour\Cdn\Helper\Data;


class FlushObserver implements ObserverInterface
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
     * @var Data
     */
    private $helper;

    /**
     * @var array
     */
    private $alreadyPurged = [];

    /*
    * @param Config $config
    * @param Api $api
    * @param Date $helper
    */
    public function __construct(
       Config $config,
       Api $api,
       Data $helper
    ) {
       $this->config = $config;
       $this->api = $api;
       $this->helper = $helper;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == Config::PEAKHOUR && $this->config->isEnabled()) {
            $object = $observer->getEvent()->getObject();

            if ($object instanceof \Magento\Framework\DataObject\IdentityInterface) {
                $tags = [];
                foreach ($object->getIdentities() as $tag) {
                    if (!in_array($tag, $this->alreadyPurged)) {
                        $tags[] = $tag;
                        $this->alreadyPurged[] = $tag;
                    }
                    else {
                        $this->helper->debug($tag . " is already flushed");
                    }

                }
                
                if (!empty($tags)) {
                    $this->helper->debug("really flushing " . implode(",", $tags));
                    $this->api->purgeTags(array_values(array_unique($tags)));
                }
            }
            else {
                $this->helper->debug("not something we can purge");
            }
        }
    }
}