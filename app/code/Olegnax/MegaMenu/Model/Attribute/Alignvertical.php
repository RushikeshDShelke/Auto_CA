<?php /**/

namespace Olegnax\MegaMenu\Model\Attribute;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Alignvertical extends AbstractSource
{
	/**
	 * Retrieve option array
	 *
	 * @return array
	 */
	public function getOptionArray()
	{
		$_options = [];
		foreach ($this->getAllOptions() as $option) {
			$_options[$option['value']] = $option['label'];
		}
		return $_options;
	}

	/**
	 * Retrieve all options array
	 *
	 * @return array
	 */
	public function getAllOptions()
	{
		if ($this->_options === null) {
			$this->_options = [
				['label' => __('menu bottom'), 'value' => 'menu-bottom'],
				['label' => __('container bottom'), 'value' => 'container-bottom'],
			];
		}
		return $this->_options;
	}

	/**
	 * Get a text for option value
	 *
	 * @param string|int $value
	 * @return string|false
	 */
	public function getOptionText($value)
	{
		$options = $this->getAllOptions();
		foreach ($options as $option) {
			if ($option['value'] == $value) {
				return $option['label'];
			}
		}
		return false;
	}

}