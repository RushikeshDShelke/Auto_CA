<?php

namespace Cminds\Marketplace\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Cminds Marketplace upgrade schema.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @param SchemaSetupInterface   $setup   Setup object.
     * @param ModuleContextInterface $context Context object.
     *
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'vendor_income',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'vendor_income',
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_supplier_shipping_methods'),
                'name',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'name',
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'shipping_price',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'shipping_price',
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'shipping_method_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'shipping_method_id',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_supplier_profile_attributes'),
                'visible_on_create_form',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 1,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Show the attribute on the supplier create form or not',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('marketplace_supplier_shipping_methods'),
                'flat_rate_fee',
                'flat_rate_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false
                ]
            );

            $setup->getConnection()->changeColumn(
                $setup->getTable('marketplace_supplier_shipping_methods'),
                'table_rate_fee',
                'table_rate_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false
                ]
            );
        }

        $setup->endSetup();
    }
}
