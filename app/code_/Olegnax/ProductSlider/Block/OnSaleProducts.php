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

use Magento\Framework\Stdlib\DateTime\DateTime;
use Zend_Db_Expr;

class OnSaleProducts extends AbstractShortcode
{

    public function getProductCollection()
    {
        $collection = parent::getProductCollection();
        $collection->addStoreFilter($this->getStoreId())
            ->addAttributeToFilter('special_from_date', ['date' => true, 'to' => $this->getDate('23:59:59')], 'left')
            ->addAttributeToFilter(
                'special_to_date', [
                'or' => [
                    0 => ['date' => true, 'from' => $this->getDate()],
                    1 => ['is' => new Zend_Db_Expr('null')],
                ]
            ], 'left')
            ->setPageSize($this->getProductsCount())
            ->distinct(true);
        $this->addAttributeToSort($collection, 'special_from_date', 'desc');

        return $collection;
    }

    public function getDate($time = '0:0:0')
    {
        return $this->_loadObject(DateTime::class)->date(null, $time);
    }

    public function getCacheKeyInfo($newval = [])
    {
        return parent::getCacheKeyInfo(['OLEGNAX_PRODUCTSLIDER_ONSALE_PRODUCTS_LIST_WIDGET']);
    }
}
