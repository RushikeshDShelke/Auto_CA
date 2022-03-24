<?php


namespace Olegnax\LayeredNavigation\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;

class CategoryDepth implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Don\'t show nested categories')],
            ['value' => 1, 'label' => __('1 level sub categories')],
            ['value' => 2, 'label' => __('2 level sub categories')],
            ['value' => 3, 'label' => __('3 level sub categories')],
            ['value' => 4, 'label' => __('4 level sub categories')],
            ['value' => 5, 'label' => __('5 level sub categories')],
        ];
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}