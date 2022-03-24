<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Catalog\Products;

class QuickviewPosition implements \Magento\Framework\Option\ArrayInterface
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
            '' => __('Default to Layout'),
            'bottom-left' => __('Bottom Left'),
            'bottom-full' => __('Bottom Full Width'),
        ];
    }
}
