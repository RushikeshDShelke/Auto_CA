<?php

namespace Wbcom\PincodeChecker\Model\ResourceModel\PincodeCheck;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init(
            'Wbcom\PincodeChecker\Model\PincodeCheck',
            'Wbcom\PincodeChecker\Model\ResourceModel\PincodeCheck'
        );
    }
}
