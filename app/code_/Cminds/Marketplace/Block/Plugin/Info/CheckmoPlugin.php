<?php

namespace Cminds\Marketplace\Block\Plugin\Info;

/**
 * Class CheckmoPlugin
 * @package Cminds\Marketplace\Block\Plugin\Info
 */
class CheckmoPlugin {
    /**
     * @param \Magento\OfflinePayments\Block\Info\Checkmo $subject
     * @param callable $proceed
     * @return \Magento\Framework\Phrase
     */
    public function aroundToPdf(\Magento\OfflinePayments\Block\Info\Checkmo $subject, callable $proceed) {
        return __('Check / Money order');
    }
}