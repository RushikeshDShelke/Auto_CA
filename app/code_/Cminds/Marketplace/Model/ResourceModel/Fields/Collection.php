<?php

namespace Cminds\Marketplace\Model\ResourceModel\Fields;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Marketplace\Model\Fields',
            'Cminds\Marketplace\Model\ResourceModel\Fields'
        );
    }
}
