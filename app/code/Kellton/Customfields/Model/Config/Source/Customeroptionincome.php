<?php

namespace Kellton\Customfields\Model\Config\Source;
 
class Customeroptionincome extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['value' => '1', 'label' => __('0-5lacs p.a.')],
                ['value' => '2', 'label' => __('5-15 lacs p.a.')],
                ['value' => '3', 'label' => __('15-30 lacs p.a.')],
                ['value' => '4', 'label' => __('30-50 lacs p.a.')],
                ['value' => '5', 'label' => __('>50 lacs p.a.')]
                
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