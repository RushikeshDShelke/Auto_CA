<?php

namespace Craft\Sorter\Plugin\Catalog\Model;

class Config
{
    public function afterGetAttributeUsedForSortByArray(
    \Magento\Catalog\Model\Config $catalogConfig,
    $options
    ) {
        unset($options['position']);
		unset($options['price']);
		$options['new_arrival'] = __('New Arrivals');
		// $options['position'] = __('Position');
        $options['low_to_high'] = __('Price - Low To High');
        $options['high_to_low'] = __('Price - High To Low');
        return $options;

    }

}