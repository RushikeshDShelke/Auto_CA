<?php

namespace Kellton\Advancecontacts\Model\ResourceModel\Advancecontact;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Advancecontacts\Model\Advancecontact', 'Kellton\Advancecontacts\Model\ResourceModel\Advancecontact');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>