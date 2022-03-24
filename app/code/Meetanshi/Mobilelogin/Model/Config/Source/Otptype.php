<?php

namespace Meetanshi\Mobilelogin\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Otptype
 * @package Meetanshi\Mobilelogin\Model\Config\Source
 */
class Otptype implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Number')],
            ['value' => '2', 'label' => __('Alphabets')],
            ['value' => '3', 'label' => __('Alphanumeric')],
        ];
    }
}
