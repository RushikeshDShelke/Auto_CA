<?php

namespace Olegnax\Athlete2\Model\Config\Settings\Header;

class SearchType implements \Magento\Framework\Option\ArrayInterface {

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
			'panel'		 => __( 'Simple Input' ),
			'overlay'	 => __( 'Fullscreen' ),
			'slideout'	 => __( 'Slideout' ),
		];
	}

}
