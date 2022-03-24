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

namespace Olegnax\BannerSlider\Ui\Component\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store {

	protected function prepareItem(array $item) {
		$content = '';
		if (!empty($item[$this->storeKey])) {
			$origStores = $item[$this->storeKey];
		} else {
			$origStores = '0';
		}

		if (!is_array($origStores)) {
			$origStores = explode(',', $origStores);
		}
		if (in_array(0, $origStores) && count($origStores) == 1) {
			return __('All Store Views');
		}

		$data = $this->systemStore->getStoresStructure(false, $origStores);

		foreach ($data as $website) {
			$content .= $website['label'] . "<br/>";
			foreach ($website['children'] as $group) {
				$content .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($group['label']) . "<br/>";
				foreach ($group['children'] as $store) {
					$content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . "<br/>";
				}
			}
		}

		return $content;
	}

}
