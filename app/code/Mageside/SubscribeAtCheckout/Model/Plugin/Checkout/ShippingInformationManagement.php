<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Model\Plugin\Checkout;

use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\ShippingInformationManagement as ShippingManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Mageside\SubscribeAtCheckout\Helper\Config as Helper;
use Mageside\SubscribeAtCheckout\Model\Config\Source\CheckoutSubscribe;

class ShippingInformationManagement
{
    /**
     * @var \Mageside\SubscribeAtCheckout\Helper\Config
     */
    protected $_helper;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @param QuoteRepository $quoteRepository
     * @param Helper $helper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Helper $helper
    ) {
        $this->_quoteRepository = $quoteRepository;
        $this->_helper = $helper;
    }

    /**
     * @param ShippingManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        ShippingManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($this->_helper->getConfigModule('enabled')) {
            $newsletterSubscribe = 0;

            if (in_array(
                $this->_helper->getConfigModule('checkout_subscribe'),
                [CheckoutSubscribe::FORCE_INVISIBLE, CheckoutSubscribe::FORCE]
            )) {
                $newsletterSubscribe = 1;
            } elseif (($extAttributes = $addressInformation->getExtensionAttributes())
                && $extAttributes->getNewsletterSubscribe()
            ) {
                $newsletterSubscribe = 1;
            }

            $quote = $this->_quoteRepository->getActive($cartId);
            $quote->setNewsletterSubscribe($newsletterSubscribe);
        }
    }
}
