<?php
namespace Kellton\Smsbulk\Model\ResourceModel;

class Smsbulk extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('smsbulk', 'id');
    }
}
?>