<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */
declare(strict_types=1);

namespace Olegnax\LayeredNavigation\Plugin\Frontend\Magento\Catalog\Model;

class CategoryRepository
{

    public function beforeGet(
        \Magento\Catalog\Model\CategoryRepository $subject,
        $categoryId,
        $storeId = null
    ) {
        if (is_array($categoryId)) {
            $categoryId = array_shift($categoryId);
        }
        return [$categoryId, $storeId];
    }
}