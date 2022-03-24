<?php /** @noinspection PhpDeprecationInspection */
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PositionV implements ArrayInterface
{
    const TOP = 'v-top';
    const BOTTOM = 'v-bottom';
    const CENTER = 'v-center';

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
                'value' => static::TOP,
                'label' => __('Top'),
            ],
            [
                'value' => static::BOTTOM,
                'label' => __('Bottom'),
            ],
            [
                'value' => static::CENTER,
                'label' => __('Center'),
            ],
        ];
    }
}

