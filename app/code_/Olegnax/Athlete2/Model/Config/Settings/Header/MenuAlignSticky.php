<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class MenuAlignSticky implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => '',     'label' => __('Same as Header (inherit)')],
            ['value' => 'left',     'label' => __('Left')],
            ['value' => 'center',  'label' => __('Center')],
            ['value' => 'right',     'label' => __('Right')]
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
