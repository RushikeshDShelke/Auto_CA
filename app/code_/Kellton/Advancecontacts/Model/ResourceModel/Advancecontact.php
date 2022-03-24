<?php
namespace Kellton\Advancecontacts\Model\ResourceModel;

class Advancecontact extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('advance_contact', 'id');
    }
}
?>