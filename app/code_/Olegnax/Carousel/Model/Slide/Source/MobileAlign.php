<?php

namespace Olegnax\Carousel\Model\Slide\Source;

class MobileAlign implements \Magento\Framework\Option\ArrayInterface 
{

	public function toOptionArray() {
		return [
			[
				'value' => 'left',
				'label' => __('Left')
			],
			[
				'value' => 'right',
				'label' => __('Right')
			],
			[
				'value' => 'center',
				'label' => __('Center')
			],
		];
	}

}