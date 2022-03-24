<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\MegaMenu\Model\Category;

/**
 * Class DataProvider
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 101.0.0
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{

	/**
	 * @return array
	 * @since 101.0.0
	 */
	protected function getFieldsMap()
	{
		$fields = parent::getFieldsMap();
		$fields['ox-menu'] = [
			'ox_title_text_color',
			'ox_title_bg_color',
			'ox_nav_custom_class',
			'ox_nav_custom_link_content',
			'ox_nav_custom_link',
			'ox_nav_custom_link_target',
			'ox_category_label',
			'ox_label_color',
			'ox_label_text_color',
			'ox_columns',
			'ox_nav_column_width',
			'ox_bg_image',
			'ox_data_tm_align_horizontal',
			'ox_nav_type',
			'ox_mm_lvl2_align_vertical',
			'ox_nav_subcategories',
			'ox_menu_width',
			'ox_cat_image',
			'ox_layout',
			'ox_nav_btm',
			'ox_nav_top',
			'ox_nav_left',
			'ox_nav_left_width',
			'ox_nav_right',
			'ox_nav_right_width',
		];
		return $fields;
	}

}
