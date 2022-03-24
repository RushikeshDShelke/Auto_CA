<?php

namespace Olegnax\BannerSlider\Ui\Component\Listing\Column;

class Group extends \Magento\Ui\Component\Listing\Columns\Column {

	protected $_objectManager;
	protected $groups;

	public function __construct(
	\Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory, array $components = [], array $data = []
	) {
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		parent::__construct($context, $uiComponentFactory, $components, $data);
		$this->setGroup();
	}

	private function setGroup() {
		if (empty($this->groups)) {
			$this->groups = [];
			$group_model = $this->_objectManager->get('\Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory')->create();
			if ($group_model && $group_model->getSize()) {
				$groups = $group_model->addFieldToSelect('*')->setOrder('group_name', 'asc');
				foreach ($groups as $group) {
					$this->groups[$group->getId()] = $group->getGroupName();
				}
			}
		}
	}

	private function getGroup($id) {
		if (array_key_exists($id, $this->groups)) {
			return $this->groups[$id];
		}
		return '';
	}

	public function prepareDataSource(array $dataSource) {
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				$item['slide_group'] = $this->getGroup($item['slide_group']);
			}
		}
		return $dataSource;
	}

}
