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

namespace Olegnax\BannerSlider\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface {

	/**
	 * {@inheritdoc}
	 */
	public function upgrade(
	ModuleDataSetupInterface $setup, ModuleContextInterface $context
	) {
		$setup->startSetup();

		$this->applyUpgradeFunctions( $setup, $context );
		$setup->endSetup();
	}

	private function getUpgradeFunctions( $low_version = '1.0.0' ) {
		$methods = get_class_methods( $this );
		foreach ( $methods as $key => $method ) {
			if ( preg_match( '/^upgrade_(.+)$/i', $method, $matches ) ) {
				if ( version_compare( $low_version, $matches[ 1 ], '<' ) ) {
					continue;
				}
			}
			unset( $methods[ $key ] );
		}

		$methods = array_filter( $methods );
		$methods = array_unique( $methods );
		sort( $methods );

		return $methods;
	}

	private function applyUpgradeFunctions( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
		$methods = $this->getUpgradeFunctions( $context->getVersion() );
		foreach ( $methods as $method ) {
			call_user_func_array( [ $this, $method ], [ $setup, $context ] );
		}
	}

}
