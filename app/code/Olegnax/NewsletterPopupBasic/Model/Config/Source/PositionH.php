<?php /** @noinspection PhpDeprecationInspection */
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PositionH implements ArrayInterface
{
    const LEFT = 'h-left';
    const RIGHT = 'h-right';
    const CENTER = 'h-center';

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
                'value' => static::LEFT,
                'label' => __('Left'),
            ],
            [
                'value' => static::RIGHT,
                'label' => __('Right'),
            ],
            [
                'value' => static::CENTER,
                'label' => __('Center'),
            ],
        ];
    }
}

