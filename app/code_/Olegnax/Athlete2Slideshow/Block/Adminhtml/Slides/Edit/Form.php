<?php

namespace Olegnax\Athlete2Slideshow\Block\Adminhtml\Slides\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {

	protected $_systemStore;

	public function __construct(
	\Magento\Backend\Block\Template\Context $context,
	\Magento\Framework\Registry $registry,
	\Magento\Framework\Data\FormFactory $formFactory,
	\Magento\Store\Model\System\Store $systemStore,
	array $data = [ ]
	) {
		$this->_systemStore = $systemStore;
		parent::__construct( $context, $registry, $formFactory, $data );
	}

	protected function _construct() {
		parent::_construct();
		$this->setId( 'slides_form' );
		$this->setTitle( __( 'Slide' ) );
	}

	protected function _prepareForm() {
		$form = $this->_formFactory->create(
		[
			'data' => [
				'id'	 => 'edit_form',
				'action' => $this->getData( 'action' ),
				'enctype'=>'multipart/form-data',
				'method' => 'post',
				
			]
		]
		);
		$form->setUseContainer( true );
		$this->setForm( $form );

		return parent::_prepareForm();
	}

}
