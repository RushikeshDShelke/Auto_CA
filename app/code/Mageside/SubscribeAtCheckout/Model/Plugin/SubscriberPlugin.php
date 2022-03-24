<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Model\Plugin;

class SubscriberPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * SubscriberPlugin constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundSendConfirmationSuccessEmail(
        \Magento\Newsletter\Model\Subscriber $subscriber,
        callable $proceed
    ) {
        $storeId = $subscriber->getStoreId();
        if ($this->scopeConfig->getValue(
            'mageside_subscribeatcheckout/general/send_success_email',
            'store',
            $storeId
        )
        ) {
            return $proceed();
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundSendConfirmationRequestEmail(
        \Magento\Newsletter\Model\Subscriber $subscriber,
        callable $proceed
    ) {
        $storeId = $subscriber->getStoreId();
        if ($this->scopeConfig->getValue(
            'mageside_subscribeatcheckout/general/send_request_email',
            'store',
            $storeId
        )
        ) {
            return $proceed();
        }
    }
}
