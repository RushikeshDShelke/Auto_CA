<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class MinicartStyle implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => 'classic',     'label' => __('Athlete Classic')],
            ['value' => 'modern',  'label' => __('Modern')],
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
