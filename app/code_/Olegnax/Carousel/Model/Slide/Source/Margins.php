<?php

namespace Olegnax\Carousel\Model\Slide\Source;

class Margins implements \Magento\Framework\Option\ArrayInterface 
{

	public function toOptionArray() {
		return [
			[
				'value' => 'normal',
				'label' => __('Normal')
			],
			[
				'value' => 'big',
				'label' => __('Big')
			],
		];
	}

}