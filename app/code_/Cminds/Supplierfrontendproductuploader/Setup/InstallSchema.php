<?php

namespace Cminds\Supplierfrontendproductuploader\Setup;

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
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable(
                    'supplierfrontendproductuploader_ordered_products'
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
                'entity_id',
                Table::TYPE_INTEGER,
                100,
                ['nullable' => false],
                'Entity Id'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Order Id'
            )
            ->addColumn(
                'supplier_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Supplier Id'
            )
            ->addColumn(
                'qty',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Qty'
            )
            ->addColumn(
                'price',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Price'
            )
            ->addColumn(
                'order_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Order Date'
            )
            ->setComment('Supplier Ordered Products');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable(
                    'supplierfrontendproductuploader_attribute_label'
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
                'attribute_id',
                Table::TYPE_INTEGER,
                100,
                ['nullable' => false],
                'Attribute Id'
            )
            ->addColumn(
                'attribute_code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Attribute Code'
            )
            ->addColumn(
                'label',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Label'
            )
            ->setComment('Supplier Attribute Label');
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addColumn(
            $installer->getTable('eav_attribute_set'),
            'available_for_supplier',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Available for Supplier',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('eav_attribute'),
            'available_for_supplier',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Available for Supplier',
            ]
        );

        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable(
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
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
