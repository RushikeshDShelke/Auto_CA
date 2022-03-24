<?php

/**
 * Olegnax BannerSlider
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_BannerSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\BannerSlider\Model;

class Group extends \Magento\Framework\Model\AbstractModel {

	protected $directoryList;
	protected $io;
	protected $_storeManager;
	protected $_eventPrefix = 'olegnax_bannerslider_group';

	public function __construct(
	\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry,
 \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Filesystem\DirectoryList $directoryList,
 \Magento\Framework\Filesystem\Io\File $io
	) {
		$this->_storeManager = $storeManager;
		$this->directoryList = $directoryList;
		$this->io			 = $io;
		parent::__construct( $context, $registry );
	}

	/**
	 * @return void
	 */
	protected function _construct() {
		$this->_init( \Olegnax\BannerSlider\Model\ResourceModel\Group::class );
	}

	public function getIdentities() {
		return [ $this->_eventPrefix . '_' . $this->getId() ];
	}

	public function getIdentifier() {
		return $this->getData( 'identifier' );
	}

}
