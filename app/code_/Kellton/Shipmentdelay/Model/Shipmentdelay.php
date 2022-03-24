<?php
namespace Kellton\Shipmentdelay\Model;

class Shipmentdelay extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Shipmentdelay\Model\ResourceModel\Shipmentdelay');
    }
}
?>