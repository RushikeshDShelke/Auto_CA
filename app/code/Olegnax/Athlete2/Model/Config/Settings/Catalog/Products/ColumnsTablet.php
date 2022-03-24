<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Catalog\Products;

class ColumnsTablet implements \Magento\Framework\Option\ArrayInterface
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
            '2' => __('2'),
            '3' => __('3'),
            '4' => __('4'),
            '5' => __('5'),
            '6' => __('6'),
        ];
    }
}
