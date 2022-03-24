<?php

namespace Cminds\Supplierfrontendproductuploader\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface;

/**
 * Cminds Supplierfrontendproductuploader upgrade schema.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
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
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            $newTable = 'supplierfrontendproductuploader_customer_sources';
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable(
                        $newTable
                    )
                )
                ->addColumn(
                    SourcesInterface::ENTITY_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'primary key'
                )->addColumn(
                    SourcesInterface::SOURCE_CODE,
                    Table::TYPE_TEXT,
                    225,
                    ['nullable' => false],
                    'Source code'
                )
                ->addColumn(
                    SourcesInterface::NAME,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Name'
                )
                ->addColumn(
                    SourcesInterface::ENABLED,
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'identity' => false, 'nullable' => false, 'default' => '0'],
                    'is enabled'
                )
                ->addColumn(
                    SourcesInterface::DESCRIPTION,
                    Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true],
                    'Description'
                )
                ->addColumn(
                    SourcesInterface::LATITUDE,
                    Table::TYPE_DECIMAL,
                    [8, 6],
                    ['nullable' => true, 'unsigned' => false ],
                    'Latitude'
                )
                ->addColumn(
                    SourcesInterface::LONGITUDE,
                    Table::TYPE_DECIMAL,
                    [9, 6],
                    ['nullable' => true, 'unsigned' => false],
                    'Longitude'
                )
                ->addColumn(
                    SourcesInterface::COUNTRY_ID,
                    Table::TYPE_TEXT,
                    2,
                    ['nullable' => false],
                    'Country ID'
                )
                ->addColumn(
                    SourcesInterface::REGION_ID,
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'identity' => false, 'nullable' => true],
                    'Region ID'
                )
                ->addColumn(
                    SourcesInterface::REGION,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Region Text'
                )
                ->addColumn(
                    SourcesInterface::CITY,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'City'
                )
                ->addColumn(
                    SourcesInterface::STREET,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Street'
                )
                ->addColumn(
                    SourcesInterface::POSTCODE,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Postcode'
                )
                ->addColumn(
                    SourcesInterface::CONTACT_NAME,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Contact Name'
                )
                ->addColumn(
                    SourcesInterface::EMAIL,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Email'
                )
                ->addColumn(
                    SourcesInterface::PHONE,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Phone'
                )
                ->addColumn(
                    SourcesInterface::FAX,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Fax'
                )
                ->addColumn(
                    SourcesInterface::CREATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created at'
                )
                ->addColumn(
                    SourcesInterface::UPDATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated at'
                )
                ->addColumn(
                    SourcesInterface::STATUS,
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'identity' => false, 'nullable' => false, 'default' => '0'],
                    'Entry status'
                )
                ->addColumn(
                    SourcesInterface::CUSTOMER_EMAIL,
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Origin Customer Email'
                )
                ->addColumn(
                    SourcesInterface::CUSTOMER_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Origin Customer Id'
                )
                ->addColumn(
                    SourcesInterface::WEBSITE_ID,
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Origin Website Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        $installer->getTable($newTable),
                        [SourcesInterface::SOURCE_CODE],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [SourcesInterface::SOURCE_CODE],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $installer->getConnection()->createTable($table);
        }


        $setup->endSetup();
    }
}
