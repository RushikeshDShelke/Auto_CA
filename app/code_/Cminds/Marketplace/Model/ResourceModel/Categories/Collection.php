<?php

namespace Cminds\Marketplace\Model\ResourceModel\Categories;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Marketplace\Model\Categories',
            'Cminds\Marketplace\Model\ResourceModel\Categories'
        );
    }
}
