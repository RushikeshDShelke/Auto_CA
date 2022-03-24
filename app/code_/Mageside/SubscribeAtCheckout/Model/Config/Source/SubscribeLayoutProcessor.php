<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Model\Config\Source;

use Mageside\SubscribeAtCheckout\Helper\Config as Helper;

/**
 * Class SubscribeLayoutProcessor
 */
class SubscribeLayoutProcessor
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $checkbox = $this->_helper->getConfigModule('checkout_subscribe');

        $checked = $checkbox == 2 ? 0 : 1;
        $visible = $checkbox == 3 ? 0 : 1;
        $changeable = $checkbox == 4 ? 0 : 1;

        if ($this->_helper->getConfigModule('enabled')) {
            $jsLayoutSubscribe = [
                'components' => [
                    'checkout' => [
                        'children' => [
                            'steps' => [
                                'children' => [
                                    'billing-step' => [
                                        'children' => [
                                            'payment' => [
                                                'children' => [
                                                    'customer-email' => [
                                                        'config' => [
                                                            'template' => 'Mageside_SubscribeAtCheckout/form/element/email'
                                                        ],
                                                        'children' => [
                                                            'newsletter-subscribe' => [
                                                                'config' => [
                                                                    'checkoutLabel' =>
                                                                        $this->_helper->getConfigModule('checkout_label'),
                                                                    'checked' => $checked,
                                                                    'visible' => $visible,
                                                                    'changeable' => $changeable,
                                                                    'template' => 'Mageside_SubscribeAtCheckout/form/element/newsletter-subscribe'
                                                                ],
                                                                'component' => 'Magento_Ui/js/form/form',
                                                                'displayArea' => 'newsletter-subscribe',
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'shipping-step' => [
                                        'children' => [
                                            'shippingAddress' => [
                                                'children' => [
                                                    'customer-email' => [
                                                        'config' => [
                                                            'template' => 'Mageside_SubscribeAtCheckout/form/element/email'
                                                        ],
                                                        'children' => [
                                                            'newsletter-subscribe' => [
                                                                'config' => [
                                                                    'checkoutLabel' =>
                                                                        $this->_helper->getConfigModule('checkout_label'),
                                                                    'checked' => $checked,
                                                                    'visible' => $visible,
                                                                    'changeable' => $changeable,
                                                                    'template' => 'Mageside_SubscribeAtCheckout/form/element/newsletter-subscribe'
                                                                ],
                                                                'component' => 'Magento_Ui/js/form/form',
                                                                'displayArea' => 'newsletter-subscribe',
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $jsLayout = array_merge_recursive($jsLayout, $jsLayoutSubscribe);
        }

        return $jsLayout;
    }
}
