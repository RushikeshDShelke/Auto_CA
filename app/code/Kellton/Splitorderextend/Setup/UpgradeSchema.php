<?php

namespace Kellton\Splitorderextend\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            
            // Get module table
            
            $tableName = $setup->getTable('sales_order');
            
            // Check if the table already exists
            
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                
                // Declare data
                
                $columns = array(
                    'has_child' => array(
                        
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        
                        'nullable' => false,
                        
                        'comment' => 'Has Child'
                        
                    ),
                    'child_ids' => array(
                        
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        
                        'nullable' => false,
                        
                        'comment' => 'Child Ids'
                        
                    ),
                    'parent_order_id' => array(
                        
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        
                        'nullable' => false,
                        
                        'comment' => 'Parent Order Id'
                        
                    )
                    
                );
                
                $connection = $setup->getConnection();
                
                foreach ($columns as $name => $definition) {
                    
                    $connection->addColumn($tableName, $name, $definition);
                    
                }
                
                
            }
            
        }
        
        
        $setup->endSetup();
        
    }
    
}
