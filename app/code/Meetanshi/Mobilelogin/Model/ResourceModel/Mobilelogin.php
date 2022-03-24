<?php

namespace Meetanshi\Mobilelogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Mobilelogin
 * @package Meetanshi\Mobilelogin\Model\ResourceModel
 */
class Mobilelogin extends AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('meetanshi_mobilelogin', 'id');
    }
}
