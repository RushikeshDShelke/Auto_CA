<?php

namespace Olegnax\Quickview\Plugin;

use \Magento\Store\Model\ScopeInterface;

class ListProduct {

	/**
	 * @var \Magento\Framework\UrlInterface 
	 */
	protected $urlInterface;

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface 
	 */
	protected $scopeConfig;

	/**
	 * @param \Magento\Framework\UrlInterface $urlInterface
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
	\Magento\Framework\UrlInterface $urlInterface, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->urlInterface	 = $urlInterface;
		$this->scopeConfig	 = $scopeConfig;
	}

	public function getConfig( $path, $storeCode = null ) {
		return $this->getSystemValue( $path, $storeCode );
	}

	public function getSystemValue( $path, $storeCode = null ) {
		return $this->scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeCode );
	}

	public function aroundGetProductDetailsHtml(
	\Magento\Catalog\Block\Product\ListProduct $subject, \Closure $proceed, \Magento\Catalog\Model\Product $product
	) {
		$result		 = $proceed( $product );
		$isEnabled	 = $this->getConfig( 'olegnax_quickview/general/enable' );
		if ( $isEnabled ) {
			$productUrl = $this->urlInterface->getUrl( 'ox_quickview/catalog_product/view', array( 'id' => $product->getId() ) );
			return $result . '<a class="ox-quickview-button" data-quickview-url="' . $productUrl . '" href="#"><span>' . __( "Quickview" ) . '</span></a>';
		}

		return $result;
	}

}
