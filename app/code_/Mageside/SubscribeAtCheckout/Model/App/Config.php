<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Model\App;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \Magento\Framework\App\Config
{
    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($path == \Magento\Newsletter\Model\Subscriber::XML_PATH_CONFIRMATION_FLAG) {
            return parent::getValue('mageside_subscribeatcheckout/general/send_request_email', $scope, $scopeCode);
        } elseif ($path == \Magento\Newsletter\Model\Subscriber::XML_PATH_SUCCESS_EMAIL_TEMPLATE) {
            return parent::getValue('mageside_subscribeatcheckout/general/send_success_email', $scope, $scopeCode);
        } else {
            return parent::getValue($path, $scope, $scopeCode);
        }
    }
}