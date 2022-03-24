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

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class FeaturedProducts extends AbstractShortcode
{

    /**
     * Prepare and return product collection
     *
     * @return Collection
     */
    public function getProductCollection()
    {
        $collection = parent::getProductCollection();

        $collection->addStoreFilter()
            ->setPageSize($this->getProductsCount())
            ->addAttributeToFilter('ox_featured', '1');

        $collection->distinct(true);
        $this->addAttributeToSort($collection);

        return $collection;
    }

    public function getCacheKeyInfo($newval = [])
    {
        return parent::getCacheKeyInfo(['OLEGNAX_PRODUCTSLIDER_FEATURED_PRODUCTS_LIST_WIDGET']);
    }

}
