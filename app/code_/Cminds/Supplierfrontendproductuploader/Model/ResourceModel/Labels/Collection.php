<?php

namespace Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Labels;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Supplierfrontendproductuploader\Model\Labels',
            'Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Labels'
        );
    }
}
