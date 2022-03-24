<?php

namespace Olegnax\BannerSlider\Block\Adminhtml\Group\Edit\Tab;

class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

	/**
	 * @var \Magento\Store\Model\System\Store
	 */
	protected $_systemStore;

	public function __construct(
		\Magento\Store\Model\System\Store $systemStore,
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\FormFactory $formFactory,
		array $data = []
	) {
		$this->_systemStore = $systemStore;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	protected function _prepareForm() {
		$model = $this->_coreRegistry->registry('olegnax_bannerslider_group');
		$form = $this->_formFactory->create();
		$fieldset = $form->addFieldset(
				'base_fieldset', ['legend' => __('General')]
		);

		if ($model->getId()) {
			$fieldset->addField(
					'group_id', 'hidden', ['name' => 'group_id']
			);
		}

		$fieldset->addField('group_name', 'text', array(
			'label' => __('Group name'),
			'name' => 'group_name',
			'required' => true
		));
		$fieldset->addField('identifier', 'text', array(
			'label' => __('Identifier'),
			'name' => 'identifier',
			'required' => true
		));
		$fieldset->addField('slide_width', 'text', array(
			'label' => __('Slide width'),
			'name' => 'slide_width',
			'required' => true
		));
		$fieldset->addField('slide_height', 'text', array(
			'label' => __('Slide height'),
			'name' => 'slide_height',
			'required' => true
		));
		
		$data = $model->getData();
		$form->setValues($data);
		$this->setForm($form);

		return parent::_prepareForm();
	}

	public function getTabLabel() {
		return __('General');
	}

	public function getTabTitle() {
		return __('General');
	}

	public function canShowTab() {
		return true;
	}

	public function isHidden() {
		return false;
	}

}
