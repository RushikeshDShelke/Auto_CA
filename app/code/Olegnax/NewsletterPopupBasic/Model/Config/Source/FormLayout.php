<?php /** @noinspection PhpDeprecationInspection */
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class FormLayout implements ArrayInterface
{
    const LAYOUT1 = 'one-line';
    const LAYOUT2 = 'button-below';

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => static::LAYOUT1,
                'label' => __('In one Line'),
            ],
            [
                'value' => static::LAYOUT2,
                'label' => __('Button Below Input'),
            ],

        ];
    }
}

