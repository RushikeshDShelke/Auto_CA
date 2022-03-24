<?php
namespace Kellton\Custominvoice\Model;

class Customcinvoice extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Custominvoice\Model\ResourceModel\Customcinvoice');
    }
}
?>