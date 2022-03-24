<?php

namespace Cminds\Marketplace\Model\ResourceModel\Rates;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Marketplace\Model\Rates',
            'Cminds\Marketplace\Model\ResourceModel\Rates'
        );
    }
}
