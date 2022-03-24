<?php

namespace Kellton\Customfields\Model\Config\Source;
 
class Customeroptioninterests extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [                
                ['value' => '1', 'label' => __('music')],
                ['value' => '2', 'label' => __('sports')],
                ['value' => '3', 'label' => __('art')],
                ['value' => '4', 'label' => __('traveling')],
                ['value' => '5', 'label' => __('social work')],
                ['value' => '6', 'label' => __('reading')],
                ['value' => '7', 'label' => __('dancing')],
                ['value' => '8', 'label' => __('others')]
                
            ];
        }
        return $this->_options;
    }
   
 
    /**
     * Get text of the option value
     * 
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionValue($value) 
    { 
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}