<?php

namespace Cminds\Supplierfrontendproductuploader\Model;

class Labels extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            'Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Labels'
        );
    }
}
