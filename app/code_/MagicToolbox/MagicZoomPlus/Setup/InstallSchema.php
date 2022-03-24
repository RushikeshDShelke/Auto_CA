<?php

namespace MagicToolbox\MagicZoomPlus\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Install Schema Script
 *
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Create table 'magiczoomplus_config'
         */
        if (!$setup->tableExists('magiczoomplus_config')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('magiczoomplus_config')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'platform',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => '0'],
                'Platform'
            )->addColumn(
                'profile',
                Table::TYPE_TEXT,
                64,
                ['nullable'  => false],
                'Profile'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                64,
                ['nullable'  => false],
                'Name'
            )->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                ['nullable'  => false],
                'Value'
            )->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => '0'],
                'Status'
            )->setComment(
                'Magic Zoom Plus configuration'
            );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
