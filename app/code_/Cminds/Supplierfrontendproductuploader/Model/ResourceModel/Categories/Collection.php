<?php

namespace Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Categories;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Supplierfrontendproductuploader\Model\Categories',
            'Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Categories'
        );
    }
}
