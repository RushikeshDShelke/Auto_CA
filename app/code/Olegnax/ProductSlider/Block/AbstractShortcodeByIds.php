<?php

/**
 * Olegnax
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
 * @package     Olegnax_ProductSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductSlider\Block;

abstract class AbstractShortcodeByIds extends AbstractShortcode
{

    protected $_atributtes = [
        'title' => '',
        'title_align' => 'center',
        'title_tag' => 'strong',
        'title_side_line' => false,
        'products_count' => 6,
        'products_ids' => '',
        'columns_desktop' => 4,
        'columns_desktop_small' => 3,
        'columns_tablet' => 2,
        'columns_mobile' => 1,
        'loop' => false,
        'arrows' => false,
        'dots' => false,
        'nav_position' => 'left-right',
        'dots_align' => 'left',
        'show_title' => true,
        'autoplay' => false,
        'autoplay_time' => '5000',
        'pause_on_hover' => false,
        'show_addtocart' => true,
        'show_wishlist' => true,
        'show_compare' => true,
        'show_review' => true,
		'show_desc' => false,
        'show_in_stock' => true,
		'rewind' => false,
        'sort_order' => '',
		'quickview_position' => '',
		'products_layout_centered' => false,
        'show_swatches' => false,
		'review_count' => false,
    ];

    public function getProductCollection()
    {
        $collection = parent::getProductCollection();
        $productIds = array_filter($this->getLoadedProductIds());
        $collection->addIdFilter($productIds);
        $productsCount = $this->getProductsCount();
        if ($productsCount) {
            $collection->setPageSize($productsCount);
        }
        $this->addAttributeToSort($collection);

        $collection->distinct(true);

        return $collection;
    }

    public function getLoadedProductIds()
    {
        $productIds = $this->getProductIds();
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }
        $productIds = array_map('intval', $productIds);
        $productIds = array_map('abs', $productIds);
        $productIds = array_filter($productIds);

        return $productIds;
    }

    public function getCacheKeyInfo($newval = [])
    {
        return parent::getCacheKeyInfo([
            'OLEGNAX_PRODUCTSLIDER_PRODUCTS_LIST_BY_ID_WIDGET',
            $this->getProductIds(),
        ]);
    }

}
