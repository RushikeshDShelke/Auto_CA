<?php

namespace Wbcom\PincodeChecker\Model\ResourceModel;

class Pincode extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_idFieldName = 'id';
    /**
     * Initialize construct Wbcom\PincodeChecker\Model\ResourceModel\Pincode
     */
    public function _construct()
    {
        $this->_init("wbcom_pincode_checker", "id");
    }
}
