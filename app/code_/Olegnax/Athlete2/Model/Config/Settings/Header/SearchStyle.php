<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class SearchStyle implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => '',     'label' => __('Default - No border')],
            ['value' => 'border-single',  'label' => __('Single Border')],
            ['value' => 'border-double',     'label' => __('Double Border')],
			['value' => 'underlined',     'label' => __('Underlined')]
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
