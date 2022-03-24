<?php /** @noinspection PhpDeprecationInspection */

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BgRepeat implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $array = $this->toArray();
        foreach ($array as $key => $value) {
            $optionArray[] = ['value' => $key, 'label' => $value];
        }

        return $optionArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'repeat' => __('Repeat'),
            'no-repeat' => __('No Repeat'),
        ];
    }

}
