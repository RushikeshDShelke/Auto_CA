<?php

namespace Kellton\Custominvoice\Model\ResourceModel\Customcminvoice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Custominvoice\Model\Customcminvoice', 'Kellton\Custominvoice\Model\ResourceModel\Customcminvoice');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>