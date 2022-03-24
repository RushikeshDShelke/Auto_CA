<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Config\Source;

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
        return [
            '' => __('Disabled'),
            static::TYPE_MAGENTO => __('Magento Default'),
            static::TYPE_IMAGE => __('CSS Preloader'),
            static::TYPE_THEME => __('Theme Preloader'),
            static::TYPE_IMAGE => __('Custom Image')
        ];
    }
}