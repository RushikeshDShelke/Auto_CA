<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class ButtonStyle implements \Magento\Framework\Option\ArrayInterface {

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
			''		 => __( 'Athlete Default (Slide from Left)' ),
			'simple'		 => __( 'Simple (Fade)' ),
		];
	}

}
