<?php

namespace Dotzot\Grid\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('wk_grid_records')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('wk_grid_records')
			)
				->addColumn(
					'entity_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Entity ID'
				)
				->addColumn(
					'sr',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false'],
					'sr'
				)
				->addColumn(
					'product',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Product'
				)
				->addColumn(
					'pincode',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'255',
					[],
					'Pincode'
				)
				->addColumn(
					'city',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'City'
				)
				->addColumn(
					'state',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'255',
					[],
					'State'
				)
				->addColumn(
					'region',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Region'
				)
				->addColumn(
					'prepaid',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Prepaid'
				)->addColumn(
					'cod',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'cod'
					)->addColumn(
					'reversepickup',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Reverse Pickup'
				)->addColumn(
					'pickup',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'pickup'	
				)->addColumn(
					'service',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Service')
				->setComment('Pincode Table');
			$installer->getConnection()->createTable($table);

		}

		$installer->endSetup();
        

    }
}
