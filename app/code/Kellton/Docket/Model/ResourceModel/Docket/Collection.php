<?php

namespace Kellton\Docket\Model\ResourceModel\Docket;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Docket\Model\Docket', 'Kellton\Docket\Model\ResourceModel\Docket');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>