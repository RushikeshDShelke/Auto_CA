<?php

namespace Kellton\Custominvoice\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('create table custom_c_invoice_number(c_incr_id int(11) unsigned zerofill NOT NULL auto_increment, invoice_number int(10) NOT NULL, c_prefix varchar(255) NOT NULL, custom_invoice_number varchar(255) NOT NULL, created_at datetime NOT NULL, primary key(c_incr_id))');
		$installer->run('create table custom_cm_invoice_number(cm_incr_id int(6) unsigned zerofill NOT NULL auto_increment, invoice_number int(11) NOT NULL, cm_prefix varchar(255) NOT NULL, custom_invoice_number varchar(255) NOT NULL, created_at datetime NOT NULL, primary key(cm_incr_id))');


		}

        $installer->endSetup();

    }
}
