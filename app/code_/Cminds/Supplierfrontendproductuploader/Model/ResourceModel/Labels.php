<?php

namespace Cminds\Supplierfrontendproductuploader\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Labels extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('supplierfrontendproductuploader_attribute_label', 'id');
    }
}
