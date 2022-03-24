<?php
namespace Kellton\Docket\Model\ResourceModel;

class Docket extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('docket', 'entity_id');
    }
}
?>