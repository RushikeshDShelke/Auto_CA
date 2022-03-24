<?php

namespace Meetanshi\Mobilelogin\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Messagetype
 * @package Meetanshi\Mobilelogin\Model\Config\Source
 */
class Messagetype implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Promotional')],
            ['value' => '4', 'label' => __('Transactional')],
        ];
    }
}
