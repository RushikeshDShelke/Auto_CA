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

namespace Olegnax\BannerSlider\Model\ResourceModel\Slides;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	/**
	 * ID Field Name
	 *
	 * @var string
	 */
	protected $_idFieldName = 'slider_id';

	/**
	 * Current scope (store Id)
	 *
	 * @var int
	 */
	protected $_storeId;

	/**
	 * Store manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	public function __construct(
	\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger,
 \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
 \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Store\Model\StoreManagerInterface $storeManager,
 \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
 \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
	) {
		$this->_storeManager = $storeManager;
		parent::__construct( $entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource );
	}

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init(
		\Olegnax\BannerSlider\Model\Slides::class, \Olegnax\BannerSlider\Model\ResourceModel\Slides::class
		);
	}

	/**
	 * Set store scope
	 *
	 * @param int|string|\Magento\Store\Api\Data\StoreInterface $storeId
	 * @return $this
	 */
	public function setStoreId( $storeId ) {
		if ( $storeId instanceof \Magento\Store\Api\Data\StoreInterface ) {
			$storeId = $storeId->getId();
		}
		$this->_storeId = (int) $storeId;
		return $this;
	}

	/**
	 * Return current store id
	 *
	 * @return int
	 */
	public function getStoreId() {
		if ( $this->_storeId === null ) {
			$this->setStoreId( $this->_storeManager->getStore()->getId() );
		}
		return $this->_storeId;
	}

	/**
	 * Add store availability filter. Include availability product
	 * for store website
	 *
	 * @param null|string|bool|int|Store $store
	 * @return $this
	 */
	public function addStoreFilter( $store = null ) {
		if ( $store === null ) {
			$store = $this->getStoreId();
		}
		$store = $this->_storeManager->getStore( $store );

		$this->getSelect()->where( "(`store_id` = 0 OR `store_id` LIKE '?,%' OR `store_id` = ? OR `store_id` LIKE '%,?' OR `store_id` LIKE '%,?,%')", $store->getId(), 'int' );

		return $this;
	}

}
