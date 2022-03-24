<?php

namespace Kellton\SupportTheArtisan\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
     public $_objectManager;

      public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager=$objectManager;
    }
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/updaterates.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);

        if(version_compare($context->getVersion(), '1.2.1', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'covid_campaign' ),
                'customer_address',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '',
                    'comment' => 'Customer Address',
                ]
            );
            
        }
        
        if (version_compare($context->getVersion(), '1.2.2')){
			 $installer->getConnection()->changeColumn(
                $installer->getTable( 'covid_campaign' ),
                'transaction_id',
                'transaction_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Transaction Id',
                ]
            );
            $logger->info('covid_campaign transaction_id column changed');
		}

        $setup->endSetup();
    }
}
