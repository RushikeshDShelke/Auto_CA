<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class BgPositionY implements \Magento\Framework\Option\ArrayInterface {

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
			'top'	 => __( 'Top' ),
			'bottom'	 => __( 'Bottom' ),
		];
	}

}
