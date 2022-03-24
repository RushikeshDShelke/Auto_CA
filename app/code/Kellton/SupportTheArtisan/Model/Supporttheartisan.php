<?php
namespace Kellton\SupportTheArtisan\Model;

class Supporttheartisan extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Kellton\SupportTheArtisan\Model\ResourceModel\Supporttheartisan');
    }
}
?>