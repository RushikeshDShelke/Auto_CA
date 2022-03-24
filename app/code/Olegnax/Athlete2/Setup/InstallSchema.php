<?php

/**
 * Olegnax
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 */

namespace Olegnax\Athlete2\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface {

	/**
	 * {@inheritdoc}
	 */
	public function install( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();
		$this->_install( $setup );
		$setup->endSetup();
	}

	public function installMagefanBlog( SchemaSetupInterface $setup ) {
		$installer = $setup;

        // $installer->startSetup();

        /**
         * Create table 'magefan_blog_post'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magefan_blog_post')
        )->addColumn(
            'post_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Post ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Post Title'
        )->addColumn(
            'meta_keywords',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Post Meta Keywords'
        )->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Post Meta Description'
        )->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true, 'default' => null],
            'Post String Identifier'
        )->addColumn(
            'content_heading',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Post Content Heading'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Post Content'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Post Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Post Modification Time'
        )->addColumn(
            'publish_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Post Publish Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Post Active'
        )->addIndex(
            $installer->getIdxName('magefan_blog_post', ['identifier']),
            ['identifier']
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('magefan_blog_post'),
                ['title', 'meta_keywords', 'meta_description', 'identifier', 'content'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['title', 'meta_keywords', 'meta_description', 'identifier', 'content'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Magefan Blog Post Table'
        );
        $installer->getConnection()->createTable($table);
	}

	private function _install( SchemaSetupInterface $setup ) {
		$tables		 = [
			'magefan_blog_post' => [
				'ox_post_list_style' => [
					'type'		 => Table::TYPE_TEXT,
					'size'		 => 255,
					'nullable'	 => true,
				],
			],
		];
		$connection	 = $setup->getConnection();
		if ( !$setup->tableExists( 'magefan_blog_post' ) ) {
			$this->installMagefanBlog( $setup );
		}

		foreach ( $tables as $table_name => $table ) {
			if ( !$setup->tableExists( $table_name ) ) {
				continue;
			}
			$_table = $setup->getTable( $table_name );
			foreach ( $table as $field_name => $field ) {
				foreach ( [
					'type'		 => Table::TYPE_INTEGER,
					'size'		 => null,
					'comment'	 => 'Added by plugin',
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
