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

use Magento\Customer\Model\Session;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;

class WishlistProducts extends ProductsByIds
{

    public function getLoadedProductIds()
    {
        $productIds = [];
        $customerSession = $this->_loadObject(Session::class);
        if ($customerSession->isLoggedIn()) {
            $_productCollection = $this->_loadObject(CollectionFactory::class)->create()
                ->addCustomerIdFilter($customerSession->getCustomerId());
            foreach ($_productCollection as $_product) {
                $productIds[] = $_product->getEntityId();
            }
        }

        return $productIds;
    }

}
