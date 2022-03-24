<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Core\Helper\Helper as CoreHelper;

class Helper extends CoreHelper
{
    const CONFIG_MODULE = 'olegnax_instagram';
    const XML_PATH_ENABLE = 'general/enabled';
    const XML_PATH_TOKEN = 'oauth/token';
    /**
     * @var \Olegnax\Athlete2\Helper\Helper
     */
    protected $athleteHelper;

    public function __construct(
        Context $context,
        \Olegnax\Athlete2\Helper\Helper $helper
    )
    {
        $this->athleteHelper = $helper;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getModuleConfig(static::XML_PATH_ENABLE);
    }

    /**
     * @param bool $update
     * @param int $storeId
     * @return string
     */
    public function generateToken($update = false, $storeId = 0)
    {
        if (empty($storeId)) {
            $storeId = (int) $this->_getRequest()->getParam('store',0);
        }
        $token = $this->getModuleConfig(static::XML_PATH_TOKEN, $storeId);
        if (empty($token) || $update) {
            $token = md5('' . rand(1, PHP_INT_MAX));
            $this->setModuleConfig(
                static::XML_PATH_TOKEN,
                $token,
                0 < $storeId ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $storeId
            );
        }

        return $token;
    }

    public function getProfileName()
    {
        /*return $this->getModuleConfig('general/use_token') ? $this->getModuleConfig('oauth/username'):$this->getModuleConfig('scraper/username');*/
		return $this->getModuleConfig('oauth/username');
    }

    /**
     * @return bool
     */
    public function isLazyLoadEnabled()
    {
        return $this->athleteHelper->isLazyLoadEnabled();
    }
}

