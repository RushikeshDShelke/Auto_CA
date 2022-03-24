<?php
namespace Olegnax\Athlete2\Model\Config\Settings\Header\Mobile;

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
            '1' => __('Logo Left'),
            '2' => __('Logo Above'),
            /*'3' => __('Menu, Logo, Search, Wishlist, Cart'),*/
			'4' => __('Search + Menu, Logo Centered, Wishlist + Cart'),
			'5' => __('Menu + Search, Logo Centered, Wishlist + Cart'),
			/*'6' => __('Menu, Logo Centered, Other Items'),*/
        ];
    }
}
