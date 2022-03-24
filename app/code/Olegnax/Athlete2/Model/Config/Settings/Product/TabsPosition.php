<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Product;

class TabsPosition implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '',     'label' => __('Default, In Content')],
            ['value' => 'info',  'label' => __('Move Tabs to Product Info block')],
            ['value' => 'oxbottom',     'label' => __('Before Page Bottom, After Content')],
			['value' => 'bottom',     'label' => __('Page Bottom')],
			['value' => 'gallery',     'label' => __('Below Gallery')]
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
