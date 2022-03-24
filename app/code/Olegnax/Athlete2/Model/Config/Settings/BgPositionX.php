<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class BgPositionX implements \Magento\Framework\Option\ArrayInterface {

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
			'center'		 => __( 'Center' ),
			'left'	 => __( 'Left' ),
			'right'	 => __( 'Right' ),
		];
	}

}
