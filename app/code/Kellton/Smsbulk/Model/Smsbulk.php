<?php
namespace Kellton\Smsbulk\Model;

class Smsbulk extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Smsbulk\Model\ResourceModel\Smsbulk');
    }
}
?>