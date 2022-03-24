<?php

namespace Olegnax\Carousel\Model\Slide\Source;

class TitleSize implements \Magento\Framework\Option\ArrayInterface 
{

	public function toOptionArray() {
		return [
			[
				'value' => '',
				'label' => __('Normal')
			],
			[
				'value' => 'big',
				'label' => __('Medium')
			],
			[
				'value' => 'huge',
				'label' => __('Big')
			],
			[
				'value' => 'huge-6vw',
				'label' => __('Huge')
			],
		];
	}

}