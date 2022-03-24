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

namespace Olegnax\BannerSlider\Model\Slides;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider {

	protected $collection;
	protected $dataPersistor;
	protected $loadedData;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $primaryFieldName
	 * @param string $requestFieldName
	 * @param CollectionFactory $collectionFactory
	 * @param DataPersistorInterface $dataPersistor
	 * @param array $meta
	 * @param array $data
	 */
	public function __construct(
	$name, $primaryFieldName, $requestFieldName, \Olegnax\BannerSlider\Model\ResourceModel\Slides\CollectionFactory $collectionFactory, \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor, array $meta = [], array $data = []
	) {
		$this->collection = $collectionFactory->create();
		$this->dataPersistor = $dataPersistor;
		parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData() {
		if (isset($this->loadedData)) {
			return $this->loadedData;
		}
		$items = $this->collection->getItems();
		foreach ($items as $model) {
			$this->loadedData[$model->getId()] = $model->getData();
		}
		$data = $this->dataPersistor->get('olegnax_bannerslider_slides');

		if (!empty($data)) {
			$model = $this->collection->getNewEmptyItem();
			$model->setData($data);
			$this->loadedData[$model->getId()] = $model->getData();
			$this->dataPersistor->clear('olegnax_bannerslider_slides');
		}

		return $this->loadedData;
	}

}
