<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Model\Config\Source;

class CheckoutSubscribe implements \Magento\Framework\Option\ArrayInterface
{

    const CHECKED = 1;
    const NOT_CHECKED = 2;
    const FORCE_INVISIBLE = 3;
    const FORCE = 4;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CHECKED, 'label' => __('Checked by default')],
            ['value' => self::NOT_CHECKED, 'label' => __('Not Checked by default')],
            ['value' => self::FORCE_INVISIBLE, 'label' => __('Force subscription not showing')],
            ['value' => self::FORCE, 'label' => __('Force subscription')]
        ];
    }
}
