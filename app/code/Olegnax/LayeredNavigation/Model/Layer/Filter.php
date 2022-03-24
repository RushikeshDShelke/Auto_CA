<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Layer;

use Magento\Framework\App\RequestInterface;

class Filter
{

    protected $request;
    protected $_ids = [];

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function getItemUrl($item)
    {
        if ($this->isSelected($item)) {
            return $item->getRemoveUrl();
        }

        return $item->getUrl();
    }

    public function getFilterValue($filter, $explode = true)
    {
        $filterValue = $this->request->getParam($filter->getRequestVar());
        if (empty($filterValue)) {
            return [];
        }
        if ($explode) {
            if (is_string($filterValue)) {
                $filterValue = explode(',', $filterValue);
            }
        }

        return $filterValue;
    }

    public function isOptionReducesResults($filter, $optionCount, $totalSize)
    {
        $result = $optionCount <= $totalSize;

        if ($this->isShowZero($filter)) {
            return $result;
        }

        return $optionCount && $result;
    }

    public function isShowZero($filter)
    {
        return false;
    }

    public function isMainFilter($filter)
    {
        if (empty($this->_ids)) {
            $this->_ids = $this->getStateAttributesIds($filter);
        }
        $isMulti = false;
        if (count($this->_ids) <= 1) {
            $isMulti = true;
            if (!empty($this->_ids)) {
                $isMulti = $this->isMultiselect($this->_ids[0]);
            }
        }

        return $isMulti;
    }

    protected function getStateAttributesIds($filter)
    {
        $layer = $filter->getLayer();
        foreach ($layer->getState()->getFilters() as $filter) {
            if ($model = $filter->getFilter()->getData('attribute_model')) {
                $this->_ids[] = $model->getId();
            }
        }
        return array_unique($this->_ids);
    }

    public function isMultiselect($attrId)
    {
        return true;
    }

}
