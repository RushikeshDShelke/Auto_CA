<?php /** @noinspection PhpDeprecationInspection */
/**
 * Copyright © Olegnax All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ManyTimes implements ArrayInterface
{
    const ONCE = 'once';
    const EVERYTIME = 'everytime';
    const RANDOM = 'random';

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
                'value' => static::ONCE,
                'label' => __('Once'),
            ],
            [
                'value' => static::EVERYTIME,
                'label' => __('Everytime'),
            ],
            [
                'value' => static::RANDOM,
                'label' => __('Random'),
            ],
        ];
    }
}

