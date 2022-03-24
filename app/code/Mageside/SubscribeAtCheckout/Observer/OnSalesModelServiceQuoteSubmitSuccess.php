<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Newsletter\Model\Subscriber;
use Mageside\SubscribeAtCheckout\Helper\Config as Helper;
use Mageside\SubscribeAtCheckout\Model\Config\Source\CheckoutSubscribe;

class OnSalesModelServiceQuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber;

    /**
     * @var \Mageside\SubscribeAtCheckout\Helper\Config
     */
    protected $_helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param Subscriber $subscriber
     * @param Helper $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Subscriber $subscriber,
        Helper $helper,
        LoggerInterface $logger
    ) {
        $this->_subscriber = $subscriber;
        $this->_helper = $helper;
        $this->_logger = $logger;
    }

    /**
     * Subscribe to newsletters if customer checked the checkbox
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->getConfigModule('enabled')) {
            $quote = $observer->getQuote();
            if ($quote->getNewsletterSubscribe()
                || in_array(
                    $this->_helper->getConfigModule('checkout_subscribe'),
                    [CheckoutSubscribe::FORCE_INVISIBLE, CheckoutSubscribe::FORCE]
                )
            ) {
                $email = 'undefined';
                try {
                    $email = $quote->getCustomerEmail();
                    if (in_array(
                        $this->_helper->getConfigModule('checkout_subscribe'),
                        [CheckoutSubscribe::FORCE_INVISIBLE, CheckoutSubscribe::FORCE]
                    )) {
                        $subscriber = $this->_subscriber->loadByEmail($email);
                        if (!$subscriber->getId()) {
                            $this->_subscriber->subscribe($email);
                        }
                    } else {
                        $this->_subscriber->subscribe($email);
                    }
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage() . 'to ' . $email);
                }
            }
        }
        
        return $this;
    }
}
