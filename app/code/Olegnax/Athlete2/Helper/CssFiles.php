<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\ScopeInterface;

class CssFiles extends AbstractHelper {

	/**
	 * Store Manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $_storeManager;
	private $google_font_enable;
	protected $store_code;

	public function __construct( Context $context, StoreManagerInterface $storeManager ) {

		$this->_storeManager = $storeManager;

		$base = BP;

		$this->webFolder		 = 'athlete2/web/';
		$this->dymanicFolder	 = 'athlete2/dymanic';
		$this->generatedCssDir	 = sprintf( '%s/pub/media/%s', $base, $this->dymanicFolder );
		parent::__construct($context);
	}

	public function getDymanicDir() {
		return $this->generatedCssDir;
	}

	public function getBaseMediaUrl( $path = '' ) {
		return $this->_storeManager->getStore()->getBaseUrl( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . $path;
	}

	public function getModuleMediaUrl( $path = '' ) {
		return $this->getBaseMediaUrl( $this->dymanicFolder ) . $path;
	}

	public function getModuleWebUrl( $path = '' ) {
		return $this->getBaseMediaUrl( $this->webFolder ) . $path;
	}

	public function getDynamicCss( $name, $code = null ) {
		return $this->getDynamicFile( $name, $code );
	}

	public function getDynamicJs( $name, $code = null ) {
		return $this->getDynamicFile( $name, $code, 'js' );
	}

	public function getDynamicFile( $name, $code = null, $format = 'css' ) {
		if ( empty( $code ) ) {
			$code = $this->_storeManager->getStore()->getCode();
		}
		return sprintf( '%s/%s_%s.%s', $this->getModuleMediaUrl(), $name, $code, $format );
	}

	public function getConfig( $path, $storeCode = null ) {
		return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	public function getFont( $font_name ) {
		$font = $this->getConfigFont( $font_name );

		if ( empty( $font ) ) {
			$default = $this->defaultFont();
			if ( array_key_exists( $font_name, $default ) ) {
				$font = $default[ $font_name ];
			}
		}

		return $font;
	}
	public function fontOutput($font_name_google, $font_name_custom){
		$font_name = 'Open Sans';
		if('' != $font_name_custom) {
			$font_name = preg_replace("/[^a-zA-Z0-9-_\s]/", "", $font_name_custom);
		} elseif($font_name_google) {
			$font_name = $font_name_google;
		}
		return '\'' . $font_name . '\', Helvetica, Arial, sans-serif';
	}
    public function getFontWeight($font_name)
    {
        $font_name = $font_name . '_weight';
        $fontWeight = $this->getConfigFont($font_name);

        if ( empty( $fontWeight ) ) {
            // fallback. use default font weight option if there are no specific weight option for selected font
            $fontWeight	 = $this->getConfigFont( 'general_font_weight' );
        }
        return $fontWeight;
    }

    public function getGoogleFontUrl()
    {
        if (!$this->isGoogleFontEnable()) {
            return '';
        }

        $default = array_keys($this->defaultFont());
        $fonts = [];
        foreach ($default as $font_name) {
            $font = $this->getFont($font_name);
            if (!empty($font)) {
                $gfw = explode(',', $this->getFontWeight($font_name));
                $ar = array_key_exists($font, $fonts) ? $fonts[$font] : [];
                $fonts[$font] = array_merge($ar, $gfw);
            }
        }
        $fonts = array_map('array_unique', $fonts);
        $fonts = array_map('array_filter', $fonts);
        $fonts = array_filter($fonts);
        foreach ($fonts as $font => $weight) {
            $fonts[$font] = sprintf('%s:%s', $font, implode(',', $weight));
        }
        $fonts = implode('|', $fonts);
        $fonts = urlencode($fonts);

        return '//fonts.googleapis.com/css?family=' . $fonts . '&display='. ($this->getConfigFont( 'fonts_display_mode' ) ?: 'fallback');
    }

	protected function getConfigFont( $font_name ) {
		$store_code			 = $this->getStoreCode();
		$appearance_general	 = $this->getConfig( 'athlete2_design/appearance_general', $store_code );

		if ( isset( $appearance_general[ $font_name ] ) && !empty( $appearance_general[ $font_name ] ) ) {
			return $appearance_general[ $font_name ];
		}

		return '';
	}

	protected function isGoogleFontEnable() {
		if ( is_null( $this->google_font_enable ) ) {
			$this->google_font_enable = $this->getConfig( 'athlete2_design/appearance_general/google_font_enable', $this->getStoreCode() );
		}
		return $this->google_font_enable;
	}
	
	public function setStoreCode( $store_code = null ) {
		$this->store_code = $store_code;
	}
	
	public function getStoreCode() {
		return $this->store_code;
	}
	

	protected function defaultFont() {
		return [
			'body_font'		 => 'Open Sans',
			'menu_font'		 => 'Open Sans',
			'general_font'	 => 'Open Sans',
			'button_font'	 => 'Open Sans',
			'title_font'	 => 'Poppins',
			'title_font_fancy' => 'Open Sans',
            'title_font_medium'	 => 'Poppins',
			'footer_content_font' => 'Open Sans',
			'copyright_content_font' => 'Open Sans',
			'sidebar_title_font'=> 'Open Sans',
		];
	}

}
