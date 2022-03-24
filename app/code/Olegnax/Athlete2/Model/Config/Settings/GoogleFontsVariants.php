<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class GoogleFontsVariants implements \Magento\Framework\Option\ArrayInterface {

	private $font_api = 'https://www.googleapis.com/webfonts/v1/webfonts?sort=alpha&key=AIzaSyAELicRrZCL8Refz-D2-FeyahisL1hUoYc';

	private function _getApiFonts() {
		$ch		 = curl_init();
		curl_setopt_array( $ch, array(
			CURLOPT_URL				 => $this->font_api,
			CURLOPT_SSL_VERIFYHOST	 => 0,
			CURLOPT_SSL_VERIFYPEER	 => 0,
			CURLOPT_RETURNTRANSFER	 => 1,
		) );
		$result	 = curl_exec( $ch );
		curl_close( $ch );

		if ( empty( $result ) ) {
			return [ ];
		}
		$fonts = json_decode( $result, true );
		if ( array_key_exists( 'items', $fonts ) ) {
			return $fonts[ 'items' ];
		}

		return [ ];
	}

	public function _mapVariant( $variant ) {
		return [
			'value'		 => $variant,
			'label'		 => $variant,
		];
	}

	private function _prepareFonts() {
		$fonts = $this->_getApiFonts();
		$variants = [];
		foreach( $fonts as $font ){
			$variants = array_merge( $variants, $font['variants'] );
		}
		$variants = array_unique( $variants );
		sort( $variants );
		$variants = array_map([$this, '_mapVariant'], $variants);

		return $variants;
	}

	public function toOptionArray() {
		$fonts = $this->_prepareFonts();

		return $fonts;
	}

	public function toArray() {
		$fonts	 = $this->toOptionArray();
		$_fonts	 = [ ];
		foreach ( $fonts as $font ) {
			$_fonts[ $font[ 'value' ] ] = $font[ 'label' ];
		}

		return $_fonts;
	}

}
