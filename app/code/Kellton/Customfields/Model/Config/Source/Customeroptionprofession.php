<?php

namespace Kellton\Customfields\Model\Config\Source;
 
class Customeroptionprofession extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['value' => '1', 'label' => __('job')],
                ['value' => '2', 'label' => __('business')],
                ['value' => '3', 'label' => __('student')],
                ['value' => '4', 'label' => __('homemaker')],
                ['value' => '5', 'label' => __('professional')],
                ['value' => '6', 'label' => __('retired')],                
                ['value' => '7', 'label' => __('others')]
                
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