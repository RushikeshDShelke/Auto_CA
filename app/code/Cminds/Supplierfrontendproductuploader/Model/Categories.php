<?php

namespace Cminds\Supplierfrontendproductuploader\Model;

class Categories extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Categories'
        );
    }
}
