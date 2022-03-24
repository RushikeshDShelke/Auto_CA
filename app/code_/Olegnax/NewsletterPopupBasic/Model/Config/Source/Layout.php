<?php /** @noinspection PhpDeprecationInspection */
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Layout implements ArrayInterface
{
    const FULLWIDTH = '-fullwidth';
    const COLUMNS_LEFT = '-columns-left';
    const COLUMNS_RIGHT = '-columns-right';

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
                'value' => static::FULLWIDTH,
                'label' => __('One Column'),
            ],
            [
                'value' => static::COLUMNS_LEFT,
                'label' => __('2 Columns, Image Left'),
            ],
            [
                'value' => static::COLUMNS_RIGHT,
                'label' => __('2 Columns, Image Right'),
            ],
        ];
    }
}

