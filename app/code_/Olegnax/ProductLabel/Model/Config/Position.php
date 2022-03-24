<?php

namespace Olegnax\ProductLabel\Model\Config;

class Position implements \Magento\Framework\Option\ArrayInterface {

	public function toOptionArray()
    {
        return [
			[
				'value' => 'top-left',
				'label' => __('Top Left')
			],
			[
				'value' => 'top-right',
				'label' => __('Top Right')
			],
			[
				'value' => 'bottom-left',
				'label' => __('Bottom Left')
			],
			[
				'value' => 'bottom-right',
				'label' => __('Bottom Right')
			]
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
