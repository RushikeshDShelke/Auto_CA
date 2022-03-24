<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->applyUpgradeFunctions($setup, $context);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    private function applyUpgradeFunctions(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $methods = $this->getUpgradeFunctions($context->getVersion());
        foreach ($methods as $method) {
            call_user_func_array([$this, $method], [$setup, $context]);
        }
    }

    /**
     * @param string $low_version
     * @return array
     */
    private function getUpgradeFunctions($low_version = '1.0.0')
    {
        $methods = get_class_methods($this);
        foreach ($methods as $key => $method) {
            if (preg_match('/^upgrade_(.+)$/i', $method, $matches)) {
                if (version_compare($low_version, $matches[1], '<')) {
                    continue;
                }
            }
            unset($methods[$key]);
        }

        $methods = array_filter($methods);
        $methods = array_unique($methods);
        sort($methods);

        return $methods;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function upgrade_99_0_1(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $tables = [
            'magefan_blog_post' => [
                'ox_post_list_style' => [
                    'type' => Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => true,
                ],
            ],
        ];
        $connection = $setup->getConnection();
        if (!$setup->tableExists('magefan_blog_post')) {
            $this->installMagefanBlog($setup);
        }

        foreach ($tables as $table_name => $table) {
            if (!$setup->tableExists($table_name)) {
                continue;
            }
            $_table = $setup->getTable($table_name);
            foreach ($table as $field_name => $field) {
                foreach ([
                             'type' => Table::TYPE_INTEGER,
                             'size' => null,
                             'comment' => 'Added by plugin',
                         ] as $attr_name => $attr) {
                    if (!array_key_exists($attr_name, $field)) {
                        $field[$attr_name] = $attr;
                    }
                }

                $connection->addColumn($_table, $field_name, $field);
            }
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    public function installMagefanBlog(SchemaSetupInterface $setup)
    {
        $installer = $setup;

        /**
         * Create table 'magefan_blog_post'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magefan_blog_post')
        )->addColumn(
            'post_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Post ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Post Title'
        )->addColumn(
            'meta_keywords',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Post Meta Keywords'
        )->addColumn(
            'meta_description',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Post Meta Description'
        )->addColumn(
            'identifier',
            Table::TYPE_TEXT,
            100,
            ['nullable' => true, 'default' => null],
            'Post String Identifier'
        )->addColumn(
            'content_heading',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Post Content Heading'
        )->addColumn(
            'content',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Post Content'
        )->addColumn(
            'creation_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Post Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Post Modification Time'
        )->addColumn(
            'publish_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Post Publish Time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
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
}
