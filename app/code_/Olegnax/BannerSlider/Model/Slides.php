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

class Slides extends \Magento\Framework\Model\AbstractModel {

	protected $directoryList;
	protected $io;
	protected $_storeManager;
	protected $_eventPrefix = 'olegnax_bannerslider_slides';

	public function __construct(
	\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Filesystem\DirectoryList $directoryList, \Magento\Framework\Filesystem\Io\File $io
	) {
		$this->_storeManager = $storeManager;
		$this->directoryList = $directoryList;
		$this->io = $io;
		parent::__construct($context, $registry);
	}

	/**
	 * @return void
	 */
	protected function _construct() {
		$this->_init(\Olegnax\BannerSlider\Model\ResourceModel\Slides::class);
	}

	public function getIdentities() {
		return [$this->_eventPrefix . '_' . $this->getId()];
	}

	private function getPrefixedPath($path, $prefix = '') {
		$fullPath = '';
		$path = ltrim($path, '/');
		if (!empty($path)) {
			$fullPath = $prefix . $path;
		}

		return $fullPath;
	}

	public function getImageUrl($fieldId = 'image') {
		return $this->getPrefixedPath($this->getData($fieldId),
						rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),
								'/') . '/');
	}

	public function getImagePath($fieldId = 'image') {
		return $this->getPrefixedPath($this->getData($fieldId),
						$this->directoryList->getRoot() . '/pub/media/');
	}

	public function existsImage($fieldId = 'image') {
		return is_file($this->getImagePath($fieldId));
	}

	public function hasImage($fieldId = 'image') {

		return ((bool) $this->getData($fieldId) !== false && $this->existsImage());
	}

}
