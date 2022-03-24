<?php

namespace Meetanshi\Mobilelogin\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Meetanshi\Mobilelogin\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Get mobilelogin table
        $tableName = $installer->getTable('meetanshi_mobilelogin');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'mobilenumber',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'MOBILENUMBER'
                )
                ->addColumn(
                    'register_otp',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'REGISTER_OTP'
                )
                ->addColumn(
                    'register_verify',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'REGISTER_VERIFY'
                )
                ->addColumn(
                    'login_otp',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'LOGIN_OTP'
                )
                ->addColumn(
                    'login_verify',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'LOGIN_VERIFY'
                )
                ->addColumn(
                    'forgot_otp',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'FORGOT_OTP'
                )
                ->addColumn(
                    'forgot_verify',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'FORGOT_VERIFY'
                )
                ->addColumn(
                    'update_otp',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'UPDATE_OTP'
                )
                ->addColumn(
                    'update_verify',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'UPDATE_VERIFY'
                )
                ->setComment('Meetanshi_Mobilelogin');

            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
