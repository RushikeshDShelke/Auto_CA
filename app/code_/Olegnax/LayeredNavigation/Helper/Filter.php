<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Helper;

use Magento\Framework\App\RequestInterface;
use Olegnax\Core\Helper\Helper as CoreHelperHelper;

class Filter extends CoreHelperHelper
{
    const CONFIG_MODULE = 'olegnax_layered_navigation';

    public function isPriceSlider()
    {
        return $this->getModuleConfig('price_slider/price_slider');
    }

    public function checkedFilter($item, $echoselected = ' checked')
    {
        $filter = $item->getFilter();
        $itemValue = $item->getValue();
        $itemValue = is_array($itemValue) ? implode('-', $itemValue) : $itemValue;
        $valueRequest = $this->getFilterValue($filter);
        if (is_array($valueRequest)) {
            $result = in_array($itemValue, $valueRequest);
        } else {
            $result = false;
        }
        if (!empty($echoselected)) {
            $result = $result ? $echoselected : '';
        }

        return $result;
    }

    public function getFilterValue($filter, $explode = true)
    {
        $filterValue = $this->getRequest()->getParam($filter->getRequestVar());
        if (empty($filterValue)) {
            return [];
        }
        if ($explode) {
            $filterValue = explode(',', $filterValue);
        }

        return $filterValue;
    }

    public function getRequest()
    {
        return $this->_loadObject(RequestInterface::class);
    }

    public function getSelectedSlider($filter)
    {
        $filterValue = $this->getRequest()->getParam($filter->getRequestVar());
        if (empty($filterValue)) {
            return '';
        }
        return $filterValue;
    }

}
