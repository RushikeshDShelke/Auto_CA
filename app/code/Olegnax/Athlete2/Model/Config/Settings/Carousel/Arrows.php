<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Carousel;

class Arrows implements \Magento\Framework\Option\ArrayInterface
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
            'title' => __('Title'),
            'hover' => __('Left/Right on Hover'),
        ];
    }
}
