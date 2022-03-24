<?php

namespace Cminds\Supplierfrontendproductuploader\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Categories extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            'supplierfrontendproductuploader_supplier_to_category',
            'id'
        );
    }
}
