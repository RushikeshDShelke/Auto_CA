<?php

/**
 * Olegnax ProductReviewsSummary
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
 * @package     Olegnax_ProductReviewsSummary
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductReviewsSummary\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
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
}
