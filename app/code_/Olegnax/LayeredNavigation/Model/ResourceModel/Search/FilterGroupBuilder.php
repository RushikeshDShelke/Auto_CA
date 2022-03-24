<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\ResourceModel\Search;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\Search\FilterGroup as SourceFilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder as SourceFilterGroupBuilder;
use Magento\Framework\App\RequestInterface;

class FilterGroupBuilder extends SourceFilterGroupBuilder
{

    protected $_request;

    public function __construct(
        ObjectFactory $objectFactory,
        FilterBuilder $filterBuilder,
        RequestInterface $request
    ) {
        parent::__construct($objectFactory, $filterBuilder);

        $this->_request = $request;
    }

    public function cloneObject()
    {
        $cloneObject = clone $this;
        $cloneObject->setFilterBuilder(clone $this->_filterBuilder);

        return $cloneObject;
    }

    public function setFilterBuilder($filterBuilder)
    {
        $this->_filterBuilder = $filterBuilder;
    }

    public function removeFilter($attributeCode)
    {
        if (isset($this->data[SourceFilterGroup::FILTERS])) {
            foreach ($this->data[SourceFilterGroup::FILTERS] as $key => $filter) {
                if ($filter->getField() == $attributeCode) {
                    if (($attributeCode == 'category_ids') && ($filter->getValue() == $this->_request->getParam('id'))) {
                        continue;
                    }
                    unset($this->data[SourceFilterGroup::FILTERS][$key]);
                }
            }
        }

        return $this;
    }

    protected function _getDataObjectType()
    {
        return 'Magento\Framework\Api\Search\FilterGroup';
    }

}
