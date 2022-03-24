<?php

namespace Kellton\Smsbulk\Model\ResourceModel\Smsbulk;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Smsbulk\Model\Smsbulk', 'Kellton\Smsbulk\Model\ResourceModel\Smsbulk');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>