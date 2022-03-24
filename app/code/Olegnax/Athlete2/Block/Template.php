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

use Magento\Customer\Model\Form;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper as HelperHelper;
use Olegnax\Athlete2\Helper\ProductImage as HelperProductImage;

class Template extends SimpleTemplate
{

	const CHILD_TEMPLATE = ChildTemplate::class;


	// Helper Product Image //

	public function getImage($product, $imageId, $template = HelperProductImage::TEMPLATE, array $attributes = [], $properties = [])
	{
		return $this->getHelperProductImage()->getImage($product, $imageId, $template,
			$attributes, $properties);
	}

	public function getHelperProductImage()
	{
		return $this->_loadObject(HelperProductImage::class);
	}

	public function getImageHover($product, $imageId, $imageId_hover, $template = HelperProductImage::HOVER_TEMPLATE, array $attributes = [], $properties = [])
	{
		return $this->getHelperProductImage()->getImageHover($product, $imageId,
			$imageId_hover, $template, $attributes, $properties);
	}

	public function getResizedImage($product, $imageId, $size, $template = HelperProductImage::TEMPLATE, array $attributes = [], $properties = [])
	{
		return $this->getHelperProductImage()->getResizedImage($product, $imageId, $size,
			$template, $attributes, $properties);
	}

	public function getResizedImageHover($product, $imageId, $imageId_hover, $size, $template = HelperProductImage::HOVER_TEMPLATE, array $attributes = [], $properties = [])
	{
		return $this->getHelperProductImage()->getResizedImageHover($product, $imageId,
			$imageId_hover, $size, $template, $attributes, $properties);
	}

	public function hasHoverImage($product, $imageId, $imageId_hover)
	{
		return $this->getHelperProductImage()->hasHoverImage($product, $imageId,
			$imageId_hover);
	}

	public function getUrlResizedImage($product, $image, $size, $properties = [])
	{
		return $this->getHelperProductImage()->getUrlResizedImage($product, $image, $size,
			$properties);
	}

	public function isLoggedIn()
	{
		return $this->getHelper()->isLoggedIn();
	}

    /**
     * @return HelperHelper
     */
	public function getHelper()
	{
		return $this->_loadObject(HelperHelper::class);
	}

	public function getWishlistCount()
	{
		return $this->getHelper()->getWishlistCount();
	}

	// Helper Helper //

	public function getCompareListUrl()
	{
		return $this->getHelper()->getCompareListUrl();
	}

	function getCompareListCount()
	{
		return $this->getHelper()->getCompareListCount();
	}

	public function isAutocompleteDisabled()
	{
		return (bool)!$this->getSystemValue(Form::XML_PATH_ENABLE_AUTOCOMPLETE);
	}

	public function getSystemValue($path, $storeCode = null)
	{
		return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	public function getBlockTemplateProcessor($content = '')
	{
		return $this->getHelper()->getBlockTemplateProcessor($content);
	}

	public function getConfig($path, $storeCode = null)
	{
		return $this->getSystemValue($path, $storeCode);
	}

	public function isCurrentPage($pageFullActionName)
	{
		if (empty($pageFullActionName)) {
			return true;
		}
		if (is_string($pageFullActionName)) {
			$pageFullActionName = explode(',', $pageFullActionName);
		}

		$pageFullActionName = array_filter($pageFullActionName);
		if (empty($pageFullActionName)) {
			return true;
		}

		return in_array($this->getFullActionName(), $pageFullActionName);
	}

	public function getFullActionName()
	{
		return $this->_getRequest()->getFullActionName();
	}

	protected function _getRequest()
	{
		return $this->_loadObject(Http::class);
	}
}
