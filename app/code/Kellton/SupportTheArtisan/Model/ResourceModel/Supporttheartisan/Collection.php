<?php

namespace Kellton\SupportTheArtisan\Model\ResourceModel\Supporttheartisan;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\SupportTheArtisan\Model\Supporttheartisan', 'Kellton\SupportTheArtisan\Model\ResourceModel\Supporttheartisan');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>