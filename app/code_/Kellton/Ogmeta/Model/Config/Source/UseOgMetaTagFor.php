<?php
/**
 * Copyright ©   All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kellton\Ogmeta\Model\Config\Source;

class UseOgMetaTagFor implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'cms_page', 'label' => __('cms_page')],['value' => 'category', 'label' => __('category')],['value' => 'product', 'label' => __('product')]];
    }

    public function toArray()
    {
        return ['cms_page' => __('cms_page'),'category' => __('category'),'product' => __('product')];
    }
}

