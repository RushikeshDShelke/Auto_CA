<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Blog;

class PageLayout implements \Magento\Framework\Option\ArrayInterface
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
			'' => __('Use Default'),
			'1column' => __('1 Column'),
            '2columns-left' => __('2 Columns with Left bar'),
            '2columns-right' => __('2 Columns with Right bar'),
			'1column-fullwidth' => __('1 Column, Full Width '),
        ];
    }
}
