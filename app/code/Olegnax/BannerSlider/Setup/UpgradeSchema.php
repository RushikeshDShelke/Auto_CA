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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

	/**
	 * {@inheritdoc}
	 */
	public function upgrade(
	SchemaSetupInterface $setup, ModuleContextInterface $context
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

	private function applyUpgradeFunctions( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$methods = $this->getUpgradeFunctions( $context->getVersion() );
		foreach ( $methods as $method ) {
			call_user_func_array( [ $this, $method ], [ $setup, $context ] );
		}
	}

	public function upgrade_1_0_1( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$tables		 = [
			'olegnax_bannerslider_group' => [
				'identifier' => [
					'type'		 => Table::TYPE_TEXT,
					'size'		 => 255,
					'nullable'	 => false,
					'default'	 => '',
					'comment'	 => 'Identifier from v1.0.1'
				],
			],
		];
		$connection	 = $setup->getConnection();
		foreach ( $tables as $table_name => $table ) {
			$_table = $setup->getTable( $table_name );
			foreach ( $table as $field_name => $field ) {
				foreach ( [
					'type'		 => Table::TYPE_INTEGER,
					'size'		 => null,
					'comment'	 => '',
				] as $attr_name => $attr ) {
					if ( !array_key_exists( $attr_name, $field ) ) {
						$field[ $attr_name ] = $attr;
					}
				}

				$connection->addColumn( $_table, $field_name, $field );
			}
		}
	}

}
