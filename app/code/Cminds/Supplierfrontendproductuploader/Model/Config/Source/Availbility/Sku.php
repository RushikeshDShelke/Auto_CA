<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Availbility;

class Sku
{
    const NOT_ALLOWED = 0;
    const ALL = 2;

    public function toOptionArray()
    {
        $options = [
            ['value' => self::NOT_ALLOWED, 'label' => __('Not allowed')],
            ['value' => self::ALL, 'label' => __('Allowed')],
        ];

        return $options;
    }
}
