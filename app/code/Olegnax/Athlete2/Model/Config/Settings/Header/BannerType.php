<?php /**/
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class BannerType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'textfield', 'label' => __('Custom HTML')],
            ['value' => 'custom_block', 'label' => __('Static Block')]
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