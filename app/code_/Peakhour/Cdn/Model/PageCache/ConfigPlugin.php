<?php

namespace Peakhour\Cdn\Model\PageCache;

use \Magento\PageCache\Model\Config;

class ConfigPlugin
{
    public function afterGetType(Config $config, $result)
    {
        if (!($config instanceof \Peakhour\Cdn\Model\Config)) {
            if ($result == \Peakhour\Cdn\Model\Config::PEAKHOUR) {
                return Config::VARNISH;
            }
        }
        return $result;
    }
}
