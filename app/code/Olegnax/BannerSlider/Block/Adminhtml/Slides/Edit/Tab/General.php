<?php

namespace Olegnax\BannerSlider\Block\Adminhtml\Slides\Edit\Tab;

class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

	/**
	 * @var \Magento\Store\Model\System\Store
	 */
	protected $_systemStore;
	protected $objectManager;

	public function __construct(
	\Magento\Store\Model\System\Store $systemStore, \Magento\Backend\Block\Template\Context $context,
 \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = []
	) {
		$this->_systemStore	 = $systemStore;
		$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		parent::__construct( $context, $registry, $formFactory, $data );
	}

	protected function _prepareForm() {
		$model		 = $this->_coreRegistry->registry( 'olegnax_bannerslider_slide' );
		$form		 = $this->_formFactory->create();
		$fieldset	 = $form->addFieldset(
		'base_fieldset', [ 'legend' => __( 'General' ) ]
		);

		if ( $model->getId() ) {
			$fieldset->addField(
			'slider_id', 'hidden', [ 'name' => 'slider_id' ]
			);
		}

		$fieldset->addField( 'slide_group', 'select', array(
			'label'	 => __( 'Slide group' ),
			'name'	 => 'slide_group',
			'values' => $this->getOptionArray( '\Olegnax\BannerSlider\Model\Config\Source\GroupName' ),
		) );

		if ( !$this->_storeManager->isSingleStoreMode() ) {
			$rendererBlock = $this->getLayout()->createBlock( 'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element' );
			$fieldset->addField( 'store_id', 'multiselect', [
				'name'		 => 'store_id',
				'label'		 => __( 'Store Views' ),
				'title'		 => __( 'Store Views' ),
				'required'	 => true,
				'values'	 => $this->_systemStore->getStoreValuesForForm( false, true )
			] )->setRenderer( $rendererBlock );
		} else {
			$fieldset->addField( 'store_id', 'hidden', [
				'name'	 => 'store_id',
				'value'	 => $this->_storeManager->getStore()->getId()
			] );
		}
		$fieldset->addField( 'slide_bg', 'text', array(
			'label'	 => __( 'Slide background' ),
			'name'	 => 'slide_bg',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'image', 'image', array(
			'label'		 => __( 'Background Image' ),
			'required'	 => true,
			'name'		 => 'image',
		) );
		$fieldset->addField( 'imageX2', 'image', array(
			'label'		 => __( 'Background Image for Retina' ),
			'required'	 => true,
			'name'		 => 'imageX2',
		) );
		$fieldset->addField( 'title_color', 'text', array(
			'label'	 => __( 'Title color' ),
			'name'	 => 'title_color',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'title_bg', 'text', array(
			'label'	 => __( 'Title background' ),
			'name'	 => 'title_bg',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'title_hover_color', 'text', array(
			'label'	 => __( 'Title Hover color' ),
			'name'	 => 'title_hover_color',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'title_hover_bg', 'text', array(
			'label'	 => __( 'Title hover background' ),
			'name'	 => 'title_hover_bg',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'title_position', 'select', [
			'name'	 => 'title_position',
			'label'	 => __( 'Title position' ),
			'values' => $this->getOptionArray( '\Olegnax\BannerSlider\Model\Config\Source\Position' ),
		] );

		$fieldset->addField( 'title', 'textarea', [
			'name'		 => 'title',
			'label'		 => __( 'Title' ),
			'required'	 => false
		] );
		$fieldset->addField( 'link_color', 'text', array(
			'label'	 => __( 'Link color' ),
			'name'	 => 'link_color',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'link_bg', 'text', array(
			'label'	 => __( 'Link background' ),
			'name'	 => 'link_bg',
			'note'	 => 'Leave empty to use default colors',
			'class'	 => 'ox-ss-colorpicker',
		) );
		$fieldset->addField( 'link_text', 'text', array(
			'label'		 => __( 'Link text' ),
			'required'	 => false,
			'name'		 => 'link_text',
		) );
		$fieldset->addField( 'link_href', 'text', array(
			'label'		 => __( 'Link Url' ),
			'required'	 => false,
			'name'		 => 'link_href',
		) );
		$fieldset->addField( 'status', 'select', [
			'name'		 => 'status',
			'label'		 => __( 'Status' ),
			'title'		 => __( 'Status' ),
			'required'	 => true,
			'options'	 => [
				'1'	 => __( 'Enable' ),
				'0'	 => __( 'Disable' )
			]
		] );
		$fieldset->addField( 'sort_order', 'text', array(
			'label'		 => __( 'Sort Order' ),
			'required'	 => false,
			'name'		 => 'sort_order',
		) );

		if ( !$model->getId() ) {
			$model->setData( 'store_id', '0' );
			$model->setData( 'status', '1' );
			$model->setData( 'sort_order', '10' );
		}



		$data = $model->getData();
		$form->setValues( $data );
		$this->setForm( $form );

		return parent::_prepareForm();
	}

	public function getTabLabel() {
		return __( 'General' );
	}

	public function getTabTitle() {
		return __( 'General' );
	}

	public function canShowTab() {
		return true;
	}

	public function isHidden() {
		return false;
	}

	private function getOptionArray( $model_name ) {
		return $this->objectManager->create( $model_name )->toOptionArray();
	}

}
