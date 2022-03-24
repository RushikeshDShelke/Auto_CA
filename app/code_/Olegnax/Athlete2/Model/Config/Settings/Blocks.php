<?php

namespace Olegnax\Athlete2\Model\Config\Settings;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Blocks implements ArrayInterface {

	protected $_options;
	/**
	 * Block collection factory
	 *
	 * @var CollectionFactory
	 */
	protected $_blockCollectionFactory;

	/**
	 * Construct
	 *
	 * @param CollectionFactory $blockCollectionFactory
	 */
	public function __construct( CollectionFactory $blockCollectionFactory ) {
		$this->_blockCollectionFactory = $blockCollectionFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toOptionArray() {
		if ( !$this->_options ) {
			$this->_options	 = [];
			$colection		 = $this->_blockCollectionFactory->create()->load();
			foreach ( $colection as $block ) {
				$this->_options[] = [ 'value' => $block->getIdentifier(), 'label' => $block->getTitle() ];
			}

			array_unshift( $this->_options, [ 'value' => '', 'label' => __( 'Please select a static block.' ) ] );
		}
		return $this->_options;
	}

}
