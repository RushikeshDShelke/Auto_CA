<?php

namespace Cminds\Marketplace\Model;

class Categories extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Cminds\Marketplace\Model\ResourceModel\Categories');
    }
}
