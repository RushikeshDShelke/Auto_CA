<?php

namespace Dotzot\Grid\Model\ResourceModel\Secondgrid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Dotzot\Grid\Model\Secondgrid',
            'Dotzot\Grid\Model\ResourceModel\Secondgrid'
        );
    }
}
