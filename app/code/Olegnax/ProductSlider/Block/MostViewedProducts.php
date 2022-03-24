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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Olegnax\ProductSlider\Model\ResourceModel\Report\Product\CollectionFactory;

class MostViewedProducts extends ProductsByIds
{
    const FLAT_CATALOG_PATH = 'catalog/frontend/flat_catalog_product';

    public function getProductCollection()
    {
        $storeId = $this->getStoreId();
        $collection = $this->_loadObject(CollectionFactory::class)->create();
        $collection->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addViewsCount()
            ->setPageSize($this->getProductsCount());
        $flatCatalogProduct = $this->_loadObject(ScopeConfigInterface::class)
            ->getValue(static::FLAT_CATALOG_PATH, ScopeInterface::SCOPE_STORE);
        if ($flatCatalogProduct) {
            $collection->getSelect()->joinLeft(
                ['flat' => 'catalog_product_flat_' . $storeId],
                "(e.entity_id = flat.entity_id ) ",
                [
                    'flat.name AS name',
                    'flat.small_image AS small_image',
                    'flat.img_hover AS img_hover',
                    'flat.ox_featured AS ox_featured',
                    'flat.price AS price',
                    'flat.special_price as special_price',
                    'flat.special_from_date AS special_from_date',
                    'flat.special_to_date AS special_to_date'
                ]
            );
        }

        return $collection;
    }

    public function getCacheKeyInfo($newval = [])
    {
        return parent::getCacheKeyInfo(['OLEGNAX_PRODUCTSLIDER_MOSTVIEWED_PRODUCTS_LIST_WIDGET']);
    }
}
