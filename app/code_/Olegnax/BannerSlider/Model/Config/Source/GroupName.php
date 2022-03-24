<?php

namespace Olegnax\BannerSlider\Model\Config\Source;

class GroupName implements \Magento\Framework\Option\ArrayInterface {

	/**
	 *
	 * @var \Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory
	 */
	protected $group;

	/**
	 *
	 * @var \Magento\Framework\App\ObjectManager
	 */
	public $_objectManager;

	public function __construct(\Olegnax\Athlete2Slideshow\Helper\Helper $helper) {
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}

	public function toOptionArray() {
		$options = [];
		if (!$this->group) {
			$this->group = $this->_objectManager->get('\Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory')->create();
		}
		if ($this->group && $this->group->getSize()) {
			$groups = $this->group->addFieldToSelect('*')->setOrder('group_name', 'asc');
			foreach ($groups as $group) {
				$options[] = [
					'value' => $group->getId(),
					'label' => $group->getGroupName()
				];
			}
		}

		return $options;
	}

}
