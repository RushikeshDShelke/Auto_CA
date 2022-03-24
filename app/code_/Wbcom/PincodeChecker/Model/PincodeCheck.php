<?php

namespace Wbcom\PincodeChecker\Model;


class PincodeCheck extends \Magento\Framework\Model\AbstractModel
{

    public function _construct()
    {
        $this->_init("Wbcom\PincodeChecker\Model\ResourceModel\PincodeCheck");
    }
}
