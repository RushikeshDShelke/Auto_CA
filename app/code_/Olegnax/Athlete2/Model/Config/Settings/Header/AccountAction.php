<?php /**/
namespace Olegnax\Athlete2\Model\Config\Settings\Header;
class AccountAction implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '',     'label' => __('Open Login/My Acount page')],
            ['value' => 'simple',  'label' => __('Simple Drop Down with Links')],
            ['value' => 'login',     'label' => __('Drop Down/Slideout with Login Form')]
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