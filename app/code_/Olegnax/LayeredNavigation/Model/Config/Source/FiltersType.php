<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class FiltersType implements ArrayInterface
{
    const TYPE_CHECKBOX = 'checkbox';

    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Simple Links')],
            ['value' => static::TYPE_CHECKBOX, 'label' => __('Checkboxes')],
        ];
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}