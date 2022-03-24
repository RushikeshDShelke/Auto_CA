<?php

namespace Cminds\Marketplace\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rates extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('marketplace_supplier_shipping_rates', 'id');
    }
}
