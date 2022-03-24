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

use Magento\Reports\Block\Product\Viewed;

class ViewedProducts extends AbstractShortcode
{

    public function getProductCollection()
    {
        $collection = $this->_loadObject(Viewed::class)
            ->getItemsCollection()
            ->setPageSize($this->getProductsCount());

        $collection->distinct(true);

        return $collection;
    }

}
