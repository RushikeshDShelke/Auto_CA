<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Product;

class Types
{
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'CONFIGURABLE',
                'label' => 'Configurable',
            ],
            [
                'value' => 'DOWNLOADABLE',
                'label' => 'Downloadable',
            ],
            [
                'value' => 'GROUPED',
                'label' => 'Grouped',
            ],
            [
                'value' => 'VIRTUAL',
                'label' => 'Virtual',
            ],
        ];

        return $options;
    }
}
