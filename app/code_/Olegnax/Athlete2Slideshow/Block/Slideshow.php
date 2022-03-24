<?php

/**
 * Olegnax Athlete Slideshow
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
 * @package     Olegnax_AthleteSlideshow
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */


namespace Olegnax\Athlete2Slideshow\Block;

use \Olegnax\Core\Block\Template as coreTemplate;
use \Olegnax\Athlete2Slideshow\Model\ResourceModel\Slides\CollectionFactory;
use \Olegnax\Athlete2Slideshow\Helper\Helper as HelperHelper;

class Slideshow extends coreTemplate {
	
	const CHILD_TEMPLATE = 'Olegnax\Athlete2Slideshow\Block\ChildTemplate';

	protected $_collection;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		CollectionFactory $collectionFactory,
		array $data = []
	) {
		$this->_collection = $collectionFactory->create();
		parent::__construct($context, $data);
	}

	public function getAthleteSlides() {
		$collection = $this->_collection;
		return $collection
						->addStoreFilter($this->_storeManager->getStore())
						->addFieldToSelect('*')
						->addFieldToFilter('status', 1)
						->setOrder('sort_order', 'asc');
	}

	public function getSlides() {
		$slides = [
		];
		switch ($this->getConfig('athleteslideshow/general/slider')) {
			case 'revolution':

				break;

			case 'athlete':
			default:
				$slides = $this->getAthleteSlides();
		}

		return $slides;
	}

	public function prepareStyle(array $style, string $separatorValue = ': ', string $separatorAttribute = ';') {
		$style = array_filter($style);
		if (empty($style)) {
			return '';
		}
		foreach ($style as $key => &$value) {
			$value = $key . $separatorValue . $value;
		}
		$style = implode($separatorAttribute, $style);

		return $style;
	}

	public function isRevoulutionActive() {
		return $this->_loadObject(HelperHelper::class)->isRevoulutionActive();
	}

	public function revolutionSliderAlias() {
		return $this->getConfig('athleteslideshow/revolutionslider/slider_id');
	}

}
