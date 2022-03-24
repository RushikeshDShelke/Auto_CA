<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Block;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\FilterList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\View\Element\Template\Context;
use Olegnax\LayeredNavigation\Helper\Helper;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{

    protected $_catalogLayer;
    protected $filterList;
    protected $visibilityFlag;
    protected $_productListHelper;
    protected $_orderField = null;
    protected $_direction = ProductList::DEFAULT_SORT_DIRECTION;
    /**
     * @var Helper
     */
    protected $_helper;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        FilterList $filterList,
        AvailabilityFlagInterface $visibilityFlag,
        ProductList $productListHelper,
        Helper $helper,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->visibilityFlag = $visibilityFlag;
        $this->_productListHelper = $productListHelper;
        $this->_helper = $helper;
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
    }

    public function AjaxEnabled()
    {
        return $this->_helper->AjaxEnabled();
    }

    public function isCategoryFilter($filter)
    {
        return ($filter->getRequestVar() == 'cat') ? true : false;
    }

    public function getFilterButtonHtml()
    {
        return $this->_helper->getFilterButtonStyle();
    }

    public function getActiveFilters()
    {
        $filters = $this->getFilters();
        $activeFilters = [];
        $ctr = 0;
        foreach ($filters as $k => $filter) {
            if ($filter->getRequestVar() == 'cat') {
                if ($filter->getItemsCount()) {
                    $ctr++;
                }
                continue;
            } else {
                if ($filter->getItemsCount()) {
                    $attributeId = $filter->getAttributeModel()->getAttributeId();
                    if ($attributeId) {
                        //$activeFilters[] = $ctr;
                    }
                    $ctr++;
                }
            }
        }

        $activeFiltersStr = implode(' ', $activeFilters);

        return $activeFiltersStr;
    }

    /**
     * Retrieve widget options in json format
     *
     * @param array $customOptions Optional parameter for passing custom selectors from template
     * @return string
     */
    public function getWidgetOptionsJson(array $customOptions = [])
    {
        $defaultMode = $this->_productListHelper->getDefaultViewMode($this->getModes());
        $options = [
            'mode' => Toolbar::MODE_PARAM_NAME,
            'direction' => Toolbar::DIRECTION_PARAM_NAME,
            'order' => Toolbar::ORDER_PARAM_NAME,
            'limit' => Toolbar::LIMIT_PARAM_NAME,
            'modeDefault' => $defaultMode,
            'directionDefault' => $this->_direction ?: ProductList::DEFAULT_SORT_DIRECTION,
            'orderDefault' => $this->getOrderField(),
            'limitDefault' => $this->_productListHelper->getDefaultLimitPerPageValue($defaultMode),
            'url' => $this->getPagerUrl(),
        ];
        $options = array_replace_recursive($options, $customOptions);
        return json_encode(['productListToolbarForm' => $options]);
    }

    protected function getOrderField()
    {
        if ($this->_orderField === null) {
            $this->_orderField = $this->_productListHelper->getDefaultSortField();
        }
        return $this->_orderField;
    }

    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

}
