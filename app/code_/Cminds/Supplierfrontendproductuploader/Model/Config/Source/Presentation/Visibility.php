<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Presentation;

class Visibility
{
    const DO_NOT_SHOW = 0;
    const SHOW_CUSTOM = 1;
    const SHOW_DEFAULT = 2;

    public function toOptionArray()
    {
        $options = [
            ['value' => self::DO_NOT_SHOW, 'label' => __("Don't show")],
            ['value' => self::SHOW_CUSTOM, 'label' => __('Show Custom')],
            ['value' => self::SHOW_DEFAULT, 'label' => __('Show Default')],
        ];

        return $options;
    }
}
