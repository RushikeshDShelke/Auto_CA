<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Delhivery\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'catalog_product_entity'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('ced_delhivery_pincode'))
            ->addColumn(
                'pincode_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'pincode_id'
            )->addColumn(
            'district',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'district'
        )->addColumn(
            'pin',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'pin'
        )->addColumn(
            'pre_paid',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'pre_paid'
        )->addColumn(
            'cash',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'cash'
        )->addColumn(
            'pickup',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'pickup'
        )->addColumn(
            'cod',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'cod'
        )->addColumn(
            'state_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'state_code'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            255,
            [],
            'status'
        )->addColumn(
            'created_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            [],
            'created_time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            [],
            'update_time'
        );
        $table2 = $installer->getConnection()
            ->newTable($installer->getTable('ced_delhivery_awb'))
            ->addColumn(
                'lastmile_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'pincode_id'
            )->addColumn(
            'awb',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'awb'
        )->addColumn(
            'shipment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            [],
            'shipment_id'
        )->addColumn(
            'shipment_to',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'shipment_to'
        )->addColumn(
            'state',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            [],
            'state'
        )->addColumn(
            'orderid',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'orderid'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'status'
        )->addColumn(
            'created_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            [],
            'created_time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            [],
            'update_time'
        );
         $installer->getConnection()->createTable($table);
         $installer->getConnection()->createTable($table2);
 
        $installer->endSetup();
    }
}
