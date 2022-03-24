<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Contacts;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray() {
		$optionArray = [ ];
		$array		 = $this->toArray();
		foreach ( $array as $key => $value ) {
			$optionArray[] = [ 'value' => $key, 'label' => $value ];
		}

		return $optionArray;
	}

    public function toArray()
    {
        return [
			'default' => __('Standard'),
            'left-right' => __('One Half Split'),
        ];
    }
}
