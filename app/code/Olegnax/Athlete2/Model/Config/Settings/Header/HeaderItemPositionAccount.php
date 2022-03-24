<?php /**/
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class HeaderItemPositionAccount implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'none',     'label' => __('Don\'t Show')],
            ['value' => 'topline',  'label' => __('in Top Line')],
            ['value' => 'main',     'label' => __('in Main Header')]
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