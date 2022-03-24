<?php

namespace Olegnax\Carousel\Model\Slide\Source;

class ButtonStyle implements \Magento\Framework\Option\ArrayInterface 
{

	public function toOptionArray() {
		return [
			[
				'value' => '',
				'label' => __('Theme')
			],
			[
				'value' => 'simple',
				'label' => __('Simple')
			],
			[
				'value' => 'naked',
				'label' => __('Naked')
			],
			[
				'value' => 'outline',
				'label' => __('OutLine')
			],
			[
				'value' => 'underline',
				'label' => __('Underlined')
			],
		];
	}

}