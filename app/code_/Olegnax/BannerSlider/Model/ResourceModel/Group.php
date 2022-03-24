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

namespace Olegnax\BannerSlider\Model\ResourceModel;

class Group extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct() {
		$this->_init('olegnax_bannerslider_group', 'group_id');
	}

	protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {
		$storeId = $object->getStoreId();
		if (is_array($storeId)) {
			$object->setStoreId(implode(',', $storeId));
		}

		return parent::_beforeSave($object);
	}

	protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object) {
		$storeId = $object->getStoreId();
		if (!is_array($storeId)) {
			$storeId = explode(',', $storeId);
			$object->setStoreId($storeId);
		}

		return parent::_afterLoad($object);
	}

}
