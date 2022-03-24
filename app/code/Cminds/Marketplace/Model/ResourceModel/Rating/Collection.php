<?php

namespace Cminds\Marketplace\Model\ResourceModel\Rating;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Marketplace\Model\Rating',
            'Cminds\Marketplace\Model\ResourceModel\Rating'
        );
    }
}
