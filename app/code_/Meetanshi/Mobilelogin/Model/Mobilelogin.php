<?php

namespace Meetanshi\Mobilelogin\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Mobilelogin
 * @package Meetanshi\Mobilelogin\Model
 */
class Mobilelogin extends AbstractModel
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Meetanshi\Mobilelogin\Model\ResourceModel\Mobilelogin');
    }
}
