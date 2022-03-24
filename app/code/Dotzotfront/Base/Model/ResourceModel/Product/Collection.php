<?php
/*


*/

namespace Dotzotfront\Base\Model\ResourceModel\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Dotzotfront\Base\Model\Product', 'Dotzotfront\Base\Model\ResourceModel\Product');
    }
}
