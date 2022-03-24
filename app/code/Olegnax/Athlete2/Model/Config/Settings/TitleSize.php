<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class TitleSize implements \Magento\Framework\Option\ArrayInterface {

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
			'h1'		 => __( 'Big (H1)' ),
			'h2'	 => __( 'Medium (H2,H3)' ),
		];
	}

}
