<?php
/*


*/

namespace Dotzotfront\Base\Model\ResourceModel;

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('plumbase_product', 'id');
    }

    /**
     * Remove old records
     *
     * @return self
     */
    public function deleteOld()
    {
        $condition = ['date < ?' => date('Y-m-d H:i:s', time() - 86400 * 30)];
        $this->getConnection()->delete($this->getMainTable(), $condition);

        return $this;
    }
}
