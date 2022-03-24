<?php

namespace Cminds\Marketplace\Model\Shipping\Calculate;

class Options
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '1',
                'label' => __('Total Shipping Cost of All Suppliers'),
            ],
            [
                'value' => '2',
                'label' => __('Highest Value'),
            ],
        ];
    }

    public function supplierOptions()
    {
        return [
            [
                'value' => '1',
                'label' => __('Fixed Price. Free Shipment Above'),
            ],
            [
                'value' => '2',
                'label' => __('Table Rate Shipping'),
            ],
            [
                'value' => '3',
                'label' => __('Per Item'),
            ],
        ];
    }
}
