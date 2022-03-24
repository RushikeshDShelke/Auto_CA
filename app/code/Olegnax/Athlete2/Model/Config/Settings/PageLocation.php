<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

use Magento\Framework\Option\ArrayInterface;

class PageLocation implements ArrayInterface {

	const HOMEPAGE	 = 'cms_index_index';
	const CATEGORY	 = 'catalog_category_view';
	const PRODUCT	 = 'catalog_product_view';
	const PAGE		 = 'cms_page_view';
	const CHECKOUT	 = 'checkout_cart_index';

	public function toOptionArray() {
		$options = [
			[
				'label'	 => __( 'All Pages' ),
				'value'	 => '',
			],
			[
				'label'	 => __( 'Home Page' ),
				'value'	 => self::HOMEPAGE,
			],
			[
				'label'	 => __( 'Category pages' ),
				'value'	 => self::CATEGORY,
			],
			[
				'label'	 => __( 'Product pages' ),
				'value'	 => self::PRODUCT,
			],
			[
				'label'	 => __( 'CMS Pages' ),
				'value'	 => self::PAGE,
			],
			[
				'label'	 => __( 'Shopping Cart' ),
				'value'	 => self::CHECKOUT,
			]
		];

		return $options;
	}

}
