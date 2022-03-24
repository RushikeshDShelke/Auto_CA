<?php

namespace Olegnax\Athlete2\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Backend system config array field renderer for integration test.
 */
class BlockArray extends AbstractFieldArray {

	/**
	 * Prepare to render
	 *
	 * @return void
	 */
	protected function _prepareToRender() {
		$this->addColumn( 'title', [ 'label' => __( 'Title' ) ] );
		$this->addColumn( 'block', [ 'label' => __( 'Block' ) ] );
		$this->addColumn( 'category_ids', [ 'label' => __( 'Categories' ) ] );
		$this->addColumn( 'product_skus', [ 'label' => __( 'Products' ) ] );
		$this->addColumn( 'sort_order', [ 'label' => __( 'Order' ) ] );
		$this->_addAfter		 = false;
		$this->_addButtonLabel	 = __( 'Add Tab' );
	}

}
