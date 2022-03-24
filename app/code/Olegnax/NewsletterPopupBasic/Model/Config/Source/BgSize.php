<?php /** @noinspection PhpDeprecationInspection */

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BgSize implements ArrayInterface
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
            'auto' => __('Auto'),
            'cover' => __('Cover'),
            'contain' => __('Contain'),
        ];
    }

}
