<?php

namespace Meetanshi\Mobilelogin\Model\ResourceModel\Mobilelogin;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Meetanshi\Mobilelogin\Model\ResourceModel\Mobilelogin
 */
class Collection extends AbstractCollection
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            'Meetanshi\Mobilelogin\Model\Mobilelogin',
            'Meetanshi\Mobilelogin\Model\ResourceModel\Mobilelogin'
        );
    }
}
