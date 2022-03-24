<?php


namespace Olegnax\ProductSlider\Model\Config\Source;


class SortOrderCat extends \Olegnax\ProductSlider\Model\Config\Source\SortOrder
{
    public const FIELD_POSITION = 'position';

    public function toArray()
    {
        $data = parent::toArray();
        $data[self::FIELD_POSITION] = __('Position');
        return $data;
    }
}