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
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

	/**
	 * {@inheritdoc}
	 */
	public function install( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();
		$this->_install( $setup );
		$setup->endSetup();
	}

	private function tables() {
		$varchar = [
			'type'		 => Table::TYPE_TEXT,
			'size'		 => 255,
			'options'	 => [
				'nullable'	 => false,
				'default'	 => '',
			],
		];
		return [
			'olegnax_bannerslider_group'	 => [
				'group_id'		 => [
					'options' => [
						'identity'	 => true,
						'nullable'	 => false,
						'primary'	 => true,
						'unsigned'	 => true,
					],
				],
				'group_name'	 => $varchar,
				'identifier'	 => $varchar,
				'slide_width'	 => [
					'type'		 => Table::TYPE_SMALLINT,
					'size'		 => 6,
					'options'	 => [
						'nullable' => false,
					],
				],
				'slide_height'	 => [
					'type'		 => Table::TYPE_SMALLINT,
					'size'		 => 6,
					'options'	 => [
						'nullable' => false,
					],
				],
				'created_time'	 => [
					'type' => Table::TYPE_DATETIME,
				],
				'update_time'	 => [
					'type' => Table::TYPE_DATETIME,
				],
			],
			'olegnax_bannerslider_slides'	 => [
				'slider_id'			 => [
					'options' => [
						'identity'	 => true,
						'nullable'	 => false,
						'primary'	 => true,
						'unsigned'	 => true,
					],
				],
				'slide_group'		 => [
					'unsigned' => true,
				],
				'store_id'			 => $varchar,
				'slide_bg'			 => $varchar,
				'image'				 => $varchar,
				'imageX2'			 => $varchar,
				'title_color'		 => $varchar,
				'title_bg'			 => $varchar,
				'title_hover_color'	 => $varchar,
				'title_hover_bg'	 => $varchar,
				'title_position'	 => $varchar,
				'title'				 => $varchar,
				'link_color'		 => $varchar,
				'link_bg'			 => $varchar,
				'link_text'			 => $varchar,
				'link_href'			 => $varchar,
				'status'			 => [
					'type'		 => Table::TYPE_SMALLINT,
					'size'		 => 6,
					'options'	 => [
						'nullable'	 => false,
						'default'	 => '0',
					],
				],
				'sort_order'		 => [
					'type'		 => Table::TYPE_SMALLINT,
					'size'		 => 6,
					'options'	 => [
						'nullable'	 => false,
						'default'	 => '0',
					],
				],
				'created_time'		 => [
					'type' => Table::TYPE_DATETIME,
				],
				'update_time'		 => [
					'type' => Table::TYPE_DATETIME,
				],
			],
		];
	}

	private function _install( SchemaSetupInterface $setup ) {
		$tables = $this->tables();
		foreach ( $tables as $table_name => $table ) {
			if ( $setup->tableExists( $table_name ) ) {
				continue;
			}
			$_table = $setup->getConnection()->newTable( $setup->getTable( $table_name ) );
			foreach ( $table as $field_name => $field ) {
				if ( !array_key_exists( 'type', $field ) ) {
					$field[ 'type' ] = Table::TYPE_INTEGER;
				}
				if ( !array_key_exists( 'size', $field ) ) {
					$field[ 'size' ] = null;
				}
				if ( !array_key_exists( 'options', $field ) || empty( $field[ 'options' ] ) ) {
					$field[ 'options' ] = [
					];
				}
				if ( !array_key_exists( 'comment', $field ) ) {
					$field[ 'comment' ] = null;
				}

				$_table->addColumn( $field_name, $field[ 'type' ], $field[ 'size' ], $field[ 'options' ], $field[ 'comment' ] );
			}
			$setup->getConnection()->createTable( $_table );
		}
	}

}
