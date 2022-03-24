<?php

namespace Cminds\Marketplace\Model;

use Magento\Framework\Model\AbstractModel;

class Payment extends AbstractModel
{
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init('Cminds\Marketplace\Model\ResourceModel\Payment');
    }
}
