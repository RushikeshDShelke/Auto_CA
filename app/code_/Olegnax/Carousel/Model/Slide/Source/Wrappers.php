<?php

namespace Olegnax\Carousel\Model\Slide\Source;

class Wrappers implements \Magento\Framework\Option\ArrayInterface 
{

	public function toOptionArray() {
		return [
			[
				'value' => 'container',
				'label' => __('Container')
			],
			[
				'value' => 'no-container',
				'label' => __('No Container')
			],
			[
				'value' => 'raw',
				'label' => __('Raw')
			],
		];
	}

}