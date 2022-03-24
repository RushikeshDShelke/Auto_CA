<?php

namespace Meetanshi\Mobilelogin\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Apiprovider
 * @package Meetanshi\Mobilelogin\Model\Config\Source
 */
class Apiprovider implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'msg91', 'label' => __('Msg91')],
            ['value' => 'textlocal', 'label' => __('Text Local')],
            ['value' => 'twilio', 'label' => __('Twilio')],
            ['value' => 'bulkpush', 'label' => __('Bulkpush')]
        ];
    }
}
