<?php

namespace Cminds\Marketplace\Model\ResourceModel\Payment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init(
            'Cminds\Marketplace\Model\Payment',
            'Cminds\Marketplace\Model\ResourceModel\Payment'
        );
    }
}
