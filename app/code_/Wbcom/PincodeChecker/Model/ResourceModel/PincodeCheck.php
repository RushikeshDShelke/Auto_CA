<?php

namespace Wbcom\PincodeChecker\Model\ResourceModel;

class PincodeCheck extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_idFieldName = 'id';

    public function _construct()
    {
        $this->_init("wbcom_pincode_avcheck", "id");
    }
}
