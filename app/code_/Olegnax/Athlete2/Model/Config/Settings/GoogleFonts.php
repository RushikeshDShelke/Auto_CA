<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

class GoogleFonts implements \Magento\Framework\Option\ArrayInterface {

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

	public function _mapFonts( $font ) {
		if ( array_key_exists( 'family', $font ) ) {
			return [
				'value'		 => $font[ 'family' ],
				'label'		 => $font[ 'family' ],
				'variants'	 => $font[ 'variants' ],
			];
		}

		return [ ];
	}

	private function _prepareFonts() {
		$fonts = $this->_getApiFonts();
		if ( !empty( $fonts ) && is_array( $fonts ) ) {
			$fonts	 = array_map( [$this, '_mapFonts' ], $fonts );
			$fonts	 = array_filter( $fonts );
		}

		return $fonts;
	}

	public function toOptionArray() {
		$fonts	 = $this->_prepareFonts();

		return $fonts;
	}

	public function toArray() {
		$fonts	 = $this->_prepareFonts();
		$_fonts = [];
		foreach ( $fonts as $font ) {
			$_fonts[$font['value']] = $font['label'];
		}

		return $_fonts;
	}
}
