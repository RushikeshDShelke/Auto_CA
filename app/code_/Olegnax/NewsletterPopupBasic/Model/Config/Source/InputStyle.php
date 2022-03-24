<?php /** @noinspection PhpDeprecationInspection */
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class InputStyle implements ArrayInterface
{
    const INHERIT = 'inherit';
    const NORMAL = 'normal';
    const UNDERLINED = 'underlined';

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
                'value' => static::NORMAL,
                'label' => __('Normal, with bg'),
            ],
            [
                'value' => static::UNDERLINED,
                'label' => __('Underlined'),
            ],
        ];
    }
}

