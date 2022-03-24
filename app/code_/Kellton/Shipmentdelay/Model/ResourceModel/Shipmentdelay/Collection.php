<?php

namespace Kellton\Shipmentdelay\Model\ResourceModel\Shipmentdelay;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Shipmentdelay\Model\Shipmentdelay', 'Kellton\Shipmentdelay\Model\ResourceModel\Shipmentdelay');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>