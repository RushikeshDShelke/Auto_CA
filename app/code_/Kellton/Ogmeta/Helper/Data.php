<?php
/**
 * Copyright ©   All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kellton\Ogmeta\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $_urlInterface;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getOgImage(){
        return $this->getConfig("ogmeta/module/def_og_img");
      }

    public function getSiteName(){
        return $this->getConfig("ogmeta/module/def_og_desc");
    }

    public function getFacebookAppId(){
        return $this->getConfig("ogmeta/module/def_og_fb_app_id");
    }

    public function getCrntUrlForOg()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    public function getStoreManagerOg(){
        return $this->_storeManager();
    }
}

