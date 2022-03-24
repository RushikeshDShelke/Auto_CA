<?php

namespace Olegnax\Athlete2\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Backend system config array field renderer for integration test.
 */
class DeferActionArray extends AbstractFieldArray {

	/**
	 * Prepare to render
	 *
	 * @return void
	 */
	protected function _prepareToRender() {
		$this->addColumn( 'condition', [ 'label' => __( 'Matched Expression' ) ] );
	}

}
