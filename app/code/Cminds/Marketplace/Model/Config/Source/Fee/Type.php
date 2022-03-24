<?php

namespace Cminds\Marketplace\Model\Config\Source\Fee;

class Type extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {
    const PERCENTAGE = 1;
    const FIXED = 2;
    
    const PRODUCT_ATTRIBUTE_FEE = 'marketplace_fee';
    const PRODUCT_ATTRIBUTE_FEE_TYPE = 'marketplace_fee_type';
    const CATEGORY_ATTRIBUTE_FEE = 'marketplace_fee';
    const CATEGORY_ATTRIBUTE_FEE_TYPE = 'marketplace_fee_type';
    const VENDOR_ATTRIBUTE_FEE = 'percentage_fee';
    const VENDOR_ATTRIBUTE_FEE_TYPE = 'fee_type';

    public function toOptionArray() {
        $options = array(
            array('value' => self::PERCENTAGE, 'label' => __('Percentage')),
            array('value' => self::FIXED, 'label' =>__('Fixed'))
        );
        return $options;
    }


    /**
     * @return array
     */
    public function getAllOptions()
    {

        $arr = [
            ['value' => self::PERCENTAGE, 'label' => __('Percentage')],
            ['value' => self::FIXED, 'label' =>__('Fixed')]
        ];
        $ret = [];

        foreach ($arr as $a) {
            $ret[] = [
                'value' => $a['value'],
                'label' => $a['label'],
            ];
        }

        return $ret;
    }

    public static function toValidate() {
        $validate = array(
            self::PERCENTAGE,
            self::FIXED
        );

        return $validate;
    }
}
