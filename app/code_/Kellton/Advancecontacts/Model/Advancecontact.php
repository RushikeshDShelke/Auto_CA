<?php
namespace Kellton\Advancecontacts\Model;

class Advancecontact extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Advancecontacts\Model\ResourceModel\Advancecontact');
    }
}
?>