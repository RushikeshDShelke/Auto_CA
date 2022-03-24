<?php
namespace Kellton\Shipmentdelay\Model\ResourceModel;

class Shipmentdelay extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shipmentdelay', 'id');
    }
}
?>