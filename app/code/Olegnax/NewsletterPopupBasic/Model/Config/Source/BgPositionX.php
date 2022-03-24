<?php /** @noinspection PhpDeprecationInspection */

namespace Olegnax\NewsletterPopupBasic\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BgPositionX implements ArrayInterface
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
            'center' => __('Center'),
            'left' => __('Left'),
            'right' => __('Right'),
        ];
    }

}
