<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class TextTransform implements \Magento\Framework\Option\ArrayInterface {

	public function toOptionArray() {
		$optionArray = [ ];
		$array		 = $this->toArray();
		foreach ( $array as $key => $value ) {
			$optionArray[] = [ 'value' => $key, 'label' => $value ];
		}

		return $optionArray;
	}

	public function toArray() {
		return [
			''		 => __( 'Choose Text Transform...' ),
			'uppercase'		 => __( 'Uppercase' ),
			'capitalize'	 => __( 'Capitalize' ),
			'initial'	 => __( 'Initial' ),
		];
	}

}
