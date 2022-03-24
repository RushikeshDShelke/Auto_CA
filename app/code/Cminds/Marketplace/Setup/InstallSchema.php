<?php

namespace Cminds\Marketplace\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @param SchemaSetupInterface   $setup   Schema setup object.
     * @param ModuleContextInterface $context Module context object.
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_supplier_shipping_methods'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'SUPPLIER ID'
            )
            ->addColumn(
                'flat_rate_available',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'flat_rate_fee',
                Table::TYPE_DECIMAL,
                [5, 2],
                ['nullable' => false]
            )
            ->addColumn(
                'table_rate_available',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'table_rate_fee',
                Table::TYPE_DECIMAL,
                [5, 2],
                ['nullable' => false]
            )
            ->addColumn(
                'table_rate_condition',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'free_shipping',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->setComment('marketplace_supplier_shipping_methods');
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_supplier_shipping_rates'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'SUPPLIER ID'
            )
            ->addColumn(
                'rate_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false]
            )
            ->setComment('marketplace_supplier_shipping_rates');
        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'vendor_fee',
            [
                'type' => Table::TYPE_DECIMAL,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'vendor_fee',
            ]
        );

        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_supplier_payments'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'SUPPLIER ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'order ID'
            )
            ->addColumn(
                'amount',
                Table::TYPE_DECIMAL,
                [5, 2],
                ['nullable' => false]
            )
            ->addColumn(
                'payment_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->setComment('marketplace_supplier_payments');
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable('marketplace_supplier_profile_attributes')
            )
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false]
            )
            ->addColumn(
                'label',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false]
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                256,
                ['nullable' => false]
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false]
            )
            ->addColumn(
                'is_required',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'is_system',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'must_be_approved',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'is_wysiwyg',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->setComment('marketplace_supplier_profile_attributes');
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_supplier_rating'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'customer ID'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'SUPPLIER ID'
            )
            ->addColumn(
                'rate',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false]
            )
            ->addColumn(
                'created_on',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->setComment('marketplace_supplier_rating');
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable('marketplace_supplier_to_rate'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'SUPPLIER ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'ORDER ID'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'PRODUCT ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'customer_id'
            )
            ->setComment('marketplace_supplier_to_rate');
        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn(
            $setup->getTable('eav_attribute_set'),
            'available_for_supplier',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'available_for_supplier',
            ]
        );

        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(
                    'supplierfrontendproductuploader_supplier_to_category'
                )
            )
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'SUPPLIER ID'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'CATEGORY ID'
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
