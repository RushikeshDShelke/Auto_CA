<?php
namespace Kellton\Custominvoice\Model\ResourceModel;

class Customcinvoice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('custom_c_invoice_number', 'c_incr_id');
    }
}
?>