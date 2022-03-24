<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Category;

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
        $fields['display_settings'][] = 'ox_nav_disable_ajax';
        return $fields;
    }

}
