<?php /**/

namespace Olegnax\MegaMenu\Model\Attribute;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Layout extends AbstractSource
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
				['label' => __('Top / Left, Categories, Right / Bottom'), 'value' => 1],
				['label' => __('Left / Top, Categories, Bottom / Right'), 'value' => 2],
				['label' => __('Left / Top, Categories / Right / Bottom'), 'value' => 3],
				['label' => __('Left / Top / Categories, Bottom / Right'), 'value' => 4],
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