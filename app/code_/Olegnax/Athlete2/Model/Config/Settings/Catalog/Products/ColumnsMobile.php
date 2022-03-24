<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Catalog\Products;

class ColumnsMobile implements \Magento\Framework\Option\ArrayInterface
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
            '1' => __('1'),
        ];
    }
}
