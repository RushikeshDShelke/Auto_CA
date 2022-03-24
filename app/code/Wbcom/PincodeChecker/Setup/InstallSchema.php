<?php

namespace Wbcom\PincodeChecker\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /*table for managing pincode list*/
        if (!$installer->tableExists('wbcom_pincode_checker')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('wbcom_pincode_checker')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    [
                        'identity'  => true,
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'pincode',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Pincode Id'
                )
                ->addColumn(
                    'delivery_days',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Delivery Days'
                )
                ->addColumn(
                    'cod',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Cash On Delivery'
                )
                ->addColumn(
                    'country_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Country Code'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'status'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                );

            $installer->getConnection()->createTable($table);
        }

        /*table for getting total pincode check*/
        if (!$installer->tableExists('wbcom_pincode_avcheck')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('wbcom_pincode_avcheck')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    [
                        'identity'  => true,
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'pincode',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Pincode Id'
                )
                ->addColumn(
                    'sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Product Sku'
                )
                ->addColumn(
                    'available_count',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                        'default'   => 0
                    ],
                    'Search Count'
                )
                ->addColumn(
                    'not_available_count',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                        'default'   => 0
                    ],
                    'Search Count'
                )
                ->addColumn(
                    'availability',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable'  => false,
                    ],
                    'Availability'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                );

            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
