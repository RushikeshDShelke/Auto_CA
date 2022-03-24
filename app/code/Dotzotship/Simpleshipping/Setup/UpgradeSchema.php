<?php

namespace Dotzotship\Simpleshipping\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
      $installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '1.2.0', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'sales_order' ),
				'docketno',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'length' => '12,4',
					'comment' => 'test',
					'after' => 'gift_message_id'
				]
			);
			$installer->getConnection()->addColumn(
				$installer->getTable( 'sales_order' ),
				'dotzotrack_status',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,
					'length' => '12,4',
					'comment' => 'test',
					'after' => 'gift_message_id'
				]
			);
		}



		$installer->endSetup();  
    }
}