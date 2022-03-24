<?php

namespace Wbcom\PincodeChecker\Model;


class Pincode extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize construct Wbcom\PincodeChecker\Model\Pincode
     */
    public function _construct()
    {
        $this->_init("Wbcom\PincodeChecker\Model\ResourceModel\Pincode");
    }
}
