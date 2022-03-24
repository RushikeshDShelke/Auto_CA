<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Plugin\Catalog\Model\Layer\Filter;

use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;
use Olegnax\LayeredNavigation\Helper\Helper;
use Olegnax\LayeredNavigation\Model\Layer\Filter;

class Item
{

    protected $_url;
    protected $_htmlPagerBlock;
    protected $_helper;
    protected $_filter;

    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock,
        Helper $helper,
        Filter $filter
    ) {
        $this->_url = $url;
        $this->_htmlPagerBlock = $htmlPagerBlock;
        $this->_helper = $helper;
        $this->_filter = $filter;
    }

    public function aroundGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
        if ($this->_helper->isEnabled()) {
            $filter = $item->getFilter();
            $itemValue = $item->getValue();
            $value = [is_array($itemValue) ? implode('-', $itemValue) : $itemValue];
            if ($this->_filter->isMultiselect($filter)) {
                $value = array_merge($this->_filter->getFilterValue($filter), $value);
                $value = array_unique($value);
            }

            $query = [
                $filter->getRequestVar() => implode(',', $value),
                // exclude current page from urls
                $this->_htmlPagerBlock->getPageVarName() => null,
            ];
            return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
        }

        return $proceed();
    }

    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
        if ($this->_helper->isEnabled()) {
            $filter = $item->getFilter();
            $itemValue = $item->getValue();
            $value = [is_array($itemValue) ? implode('-', $itemValue) : $itemValue];
            $valueRequest = $this->_filter->getFilterValue($filter);
            if (in_array($value[0], $valueRequest)) {
                $valueRequest = array_diff($valueRequest, $value);
            }

            $query = [
                $filter->getRequestVar() => count($valueRequest) ? implode(',', $valueRequest) :
                    $filter->getResetValue()
            ];
            $params['_current'] = true;
            $params['_use_rewrite'] = true;
            $params['_query'] = $query;
            $params['_escape'] = true;
            return $this->_url->getUrl('*/*/*', $params);
        }

        return $proceed();
    }

}
