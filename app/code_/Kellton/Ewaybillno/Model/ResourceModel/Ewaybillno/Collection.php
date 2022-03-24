<?php

namespace Kellton\Ewaybillno\Model\ResourceModel\Ewaybillno;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Ewaybillno\Model\Ewaybillno', 'Kellton\Ewaybillno\Model\ResourceModel\Ewaybillno');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>