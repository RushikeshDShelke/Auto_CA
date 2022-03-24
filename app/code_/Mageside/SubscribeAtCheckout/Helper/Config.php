<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get module settings
     *
     * @param $key
     * @return mixed
     */
    public function getConfigModule($key)
    {
        return $this->scopeConfig->getValue(
            'mageside_subscribeatcheckout/general/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
