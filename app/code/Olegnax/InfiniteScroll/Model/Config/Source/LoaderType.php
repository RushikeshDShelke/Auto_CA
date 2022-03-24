<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LoaderType implements ArrayInterface
{
	const TYPE_THEME = 'theme';
	const TYPE_MAGENTO = 'magento';
	const TYPE_BLOCK = 'css';
	const TYPE_IMAGE = 'image';

    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Disabled')],
            ['value' => static::TYPE_MAGENTO, 'label' => __('Magento Default')],
            ['value' => static::TYPE_BLOCK, 'label' => __('CSS Preloader')],
            ['value' => static::TYPE_THEME, 'label' => __('Theme Preloader')],
            ['value' => static::TYPE_IMAGE, 'label' => __('Custom Image')]
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