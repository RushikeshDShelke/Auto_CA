<?php

namespace Olegnax\Athlete2Slideshow\Block\Adminhtml\Slides\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs {

	protected function _construct() {
		parent::_construct();
		$this->setId( 'slides_edit_tabs' );
		$this->setDestElementId( 'edit_form' );
		$this->setTitle( __( 'Slide' ) );
	}

}
