<?php

namespace MagicToolbox\MagicZoomPlus\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MagicToolbox\MagicZoomPlus\Model\Config', 'MagicToolbox\MagicZoomPlus\Model\ResourceModel\Config');
    }
}
