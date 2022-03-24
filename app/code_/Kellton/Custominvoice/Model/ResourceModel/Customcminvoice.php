<?php
namespace Kellton\Custominvoice\Model\ResourceModel;

class Customcminvoice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('custom_cm_invoice_number', 'cm_incr_id');
    }
}
?>