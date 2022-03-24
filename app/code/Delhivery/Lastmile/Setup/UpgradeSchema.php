<?php

namespace Delhivery\Lastmile\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements  UpgradeSchemaInterface

{

public function upgrade(SchemaSetupInterface $setup,

ModuleContextInterface $context){

$setup->startSetup();

if (version_compare($context->getVersion(), '1.3.1') < 0) {

// Get module table

$tableName = $setup->getTable('delhivery_lastmile_awb');

// Check if the table already exists

if ($setup->getConnection()->isTableExists($tableName) == true) {

// Declare data

$columns = [

'shipping_amount' => [

'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,

'nullable' => true,

'comment' => 'Manage AWB Shipping Amount',

],

];

$connection = $setup->getConnection();

foreach ($columns as $name => $definition) {

$connection->addColumn($tableName, $name, $definition);

}

 
}

}

 
$setup->endSetup();

}

}