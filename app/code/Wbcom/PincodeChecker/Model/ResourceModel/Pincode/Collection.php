<?php

namespace Wbcom\PincodeChecker\Model\ResourceModel\Pincode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    /**
     * Initialize construct
     * Initialize Pincode models
     */
    public function _construct()
    {
        $this->_init(
            'Wbcom\PincodeChecker\Model\Pincode',
            'Wbcom\PincodeChecker\Model\ResourceModel\Pincode'
        );
    }
}
