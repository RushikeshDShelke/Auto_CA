<?php

/**
 * Athlete2 Theme
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
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class SimpleTemplate extends \Magento\Framework\View\Element\Template
{

	public function isLoggedIn()
	{
		return $this->getSession()->isLoggedIn();
	}

	public function getSession()
	{
		return ObjectManager::getInstance()->create(Session::class);
	}

	public function getConfig($path, $storeCode = null)
	{
		return $this->getSystemValue($path, $storeCode);
	}

	public function getSystemValue($path, $storeCode = null)
	{
		return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	protected function _loadObject($object)
	{
		return $this->_getObjectManager()->get($object);
	}

	protected function _getObjectManager()
	{
		return ObjectManager::getInstance();
	}
}
