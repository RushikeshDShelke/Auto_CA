<?php /**/

namespace Olegnax\MegaMenu\Model\Attribute;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Alignhorizontal extends AbstractSource
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
				['label' => __('item-left'), 'value' => 'item-left'],
				['label' => __('item-right'), 'value' => 'item-right'],
				['label' => __('item-center'), 'value' => 'item-center'],
				['label' => __('menu-left'), 'value' => 'menu-left'],
				['label' => __('menu-right'), 'value' => 'menu-right'],
				['label' => __('menu-center'), 'value' => 'menu-center'],
				['label' => __('window-left'), 'value' => 'window-left'],
				['label' => __('window-right'), 'value' => 'window-right'],
				['label' => __('window-center'), 'value' => 'window-center'],
				['label' => __('container-left'), 'value' => 'container-left'],
				['label' => __('container-right'), 'value' => 'container-right'],
				['label' => __('container-center'), 'value' => 'container-center'],
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