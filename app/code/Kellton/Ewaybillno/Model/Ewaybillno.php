<?php
namespace Kellton\Ewaybillno\Model;

class Ewaybillno extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Ewaybillno\Model\ResourceModel\Ewaybillno');
    }
}
?>