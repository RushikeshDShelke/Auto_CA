<?php

namespace Cminds\Marketplace\Model;

class Rating extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Cminds\Marketplace\Model\ResourceModel\Rating');
    }
}
