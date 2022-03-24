<?php
namespace Kellton\Ewaybillno\Model\ResourceModel;

class Ewaybillno extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_flat_shipment_ewaybillno', 'id');
    }
}
?>