<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class BgSize implements \Magento\Framework\Option\ArrayInterface {

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
			'auto'		 => __( 'Auto' ),
			'cover'	 => __( 'Cover' ),
			'contain'	 => __( 'Contain' ),
		];
	}

}
