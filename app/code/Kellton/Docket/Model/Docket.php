<?php
namespace Kellton\Docket\Model;

class Docket extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\Docket\Model\ResourceModel\Docket');
    }
}
?>