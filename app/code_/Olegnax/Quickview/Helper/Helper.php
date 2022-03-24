<?php

/**
 * A Magento 2 module named Olegnax/Quickview
 * Copyright (C) 2021
 *
 * This file is part of Olegnax/Quickview.
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
 * @category    Helper
 * @package     Quickview
 * @copyright   Copyright (c) 2010-2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Quickview\Helper;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Helper extends AbstractHelper
{

	public function getConfig($path, $storeCode = null)
	{
		return $this->getSystemValue($path, $storeCode);
	}

	public function getSystemValue($path, $storeCode = null)
	{
		return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	public function getPostDataParams($product)
	{
		$data['product'] = $product->getId();
		if ($this->_getRequest()->getServer('HTTP_REFERER')) {
			$data[ActionInterface::PARAM_NAME_URL_ENCODED] = base64_encode($this->_getRequest()->getServer('HTTP_REFERER'));
		} else {
			$data[ActionInterface::PARAM_NAME_URL_ENCODED] = base64_encode($this->_getUrl(''));
		}
		return json_encode(['action' => $this->_getUrl('catalog/product_compare/add'), 'data' => $data]);
	}

	public function getButton($product = null, $customClasses = '', $template = '')
	{
		$productUrl = $this->getUrl($product);
		if (!empty($productUrl)) {
			$customClasses = trim($customClasses);
			$customClasses = !empty($customClasses) ? $customClasses . ' ' : '';
			if (empty($template)) {
				$template = '<a class="{{customClasses}}ox-quickview-button" data-quickview-url="{{productUrl}}" href="#"><span>' . __("Quickview") . '</span></a>';
			}
			return str_replace(['{{customClasses}}', '{{productUrl}}'], [$customClasses, $productUrl], $template);
		}

		return '';
	}

	public function getUrl($product = null)
	{
		if (empty($product)) {
			$product = $this->getProduct();
		}
		$isEnabled = $this->getConfig('olegnax_quickview/general/enable') && !empty($product);
		if ($isEnabled) {
			$urlInterface = $this->_loadObject(UrlInterface::class);
			return $urlInterface->getUrl('ox_quickview/catalog_product/view', array('id' => $product->getId()));
		}
		return '';
	}

	public function getProduct()
	{
		$register = $this->_loadObject(Registry::class);
		$product = $register->registry('product');
		if ($product) {
			return $product;
		}
		$product = $register->registry('current_product');
		if ($product) {
			return $product;
		}

		return null;
	}

	protected function _getObjectManager()
	{
		return ObjectManager::getInstance();
	}

	protected function _loadObject( $object ) {
		return $this->_getObjectManager()->get( $object );
	}

}
