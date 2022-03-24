<?php


namespace Olegnax\ProductSlider\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;

class SortOrder implements ArrayInterface
{
    public const FIELD_DEFAULT = '';
    public const FIELD_NAME = 'name';
    public const FIELD_PRICE_ASC = 'price_asc';
    public const FIELD_PRICE_DESC = 'price_desc';
    public const FIELD_CREATED = 'created_at';

    public function toOptionArray()
    {
        $optionArray = [];
        $array = $this->toArray();
        foreach ($array as $key => $value) {
            $optionArray[] = ['value' => $key, 'label' => $value];
        }

        return $optionArray;
    }

    public function toArray()
    {
        return [
            self::FIELD_DEFAULT => __('Default'),
            self::FIELD_NAME => __('Name'),
            self::FIELD_PRICE_ASC => __('Price: high to low'),
            self::FIELD_PRICE_DESC => __('Price: low to high'),
            self::FIELD_CREATED => __('Created'),
        ];
    }
}