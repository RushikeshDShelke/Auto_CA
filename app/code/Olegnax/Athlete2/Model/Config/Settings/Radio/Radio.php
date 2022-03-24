<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Radio;

class Radio implements \Magento\Framework\Option\ArrayInterface
{
   public function toOptionArray()
	{
    	return [['value' => 'left', 'label' => __('Left')], ['value' => 'right', 'label' => __('Right')]];
	}
}