<?php

namespace Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Sources;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Supplierfrontendproductuploader\Model\Sources',
            'Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Sources'
        );
    }
}
