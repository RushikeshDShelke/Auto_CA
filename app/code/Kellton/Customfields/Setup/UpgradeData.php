<?php

namespace Kellton\Customfields\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

/**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
 
    /**
     * EAV setup factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;
 
    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        /**
         * run this code if the module version stored in database is less than 1.0.1
         * i.e. the code is run while upgrading the module from version 1.0.0 to 1.0.1
         * 
         * you can write the version_compare function in the following way as well:
         * if(version_compare($context->getVersion(), '1.0.1', '<')) { 
         * 
         * the syntax is only different
         * output is the same
         */ 
       
        if(version_compare($context->getVersion(), '1.1.2', '<')) { 
            
            $attributeCode1 = 'income';            
            // add/update Source_model to the attribute
            $customerSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY, // customer entity code
                $attributeCode1,
                'source_model',
                Kellton\Customfields\Model\Config\Source\Customeroptionincome::class
            ); 
           
           $attributeCode2 = 'profession';            
            // add/update Source_model to the attribute
            $customerSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY, // customer entity code
                $attributeCode2,
                'source_model',
                Kellton\Customfields\Model\Config\Source\Customeroptionprofession::class
            ); 

            $attributeCode3 = 'interests';            
            // add/update Source_model to the attribute
            $customerSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY, // customer entity code
                $attributeCode3,
                'source_model',
                Kellton\Customfields\Model\Config\Source\Customeroptioninterests::class
            ); 
        }
 
 
        $setup->endSetup();
    }
}