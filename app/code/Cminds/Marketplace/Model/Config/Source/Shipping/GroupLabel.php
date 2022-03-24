<?php

namespace Cminds\Marketplace\Model\Config\Source\Shipping;

class GroupLabel
{
    const FIRST_NAME = 0;
    const PROFILE_NAME = 1;

    public function toOptionArray()
    {
        $options = [
            ['value' => self::FIRST_NAME, 'label' => __('First Name + Last Name')],
            ['value' => self::PROFILE_NAME, 'label' => __('Profile Name')]
        ];

        return $options;
    }
}
