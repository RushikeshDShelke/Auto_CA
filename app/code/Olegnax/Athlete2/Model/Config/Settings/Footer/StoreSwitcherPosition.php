<?php /**/
namespace Olegnax\Athlete2\Model\Config\Settings\Footer;
class StoreSwitcherPosition implements \Magento\Framework\Option\ArrayInterface
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
            'below-newsletter' => __('Below Footer Newsletter Block'),
            'footer-content' => __('Below Footer Content'),
            'custom' => __('Custom/Do not display'),
        ];
    }
}