<?php

namespace Olegnax\ProductLabel\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $this->_install($setup);
        $setup->endSetup();
    }

    private function _install(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create([
            'setup' => $setup,
        ]);
        $tablefields = $this->fields();
        foreach ($tablefields as $entity => $fields) {
            foreach ($fields as $key => $value) {
                $eavSetup->addAttribute($entity, $key, $value);
            }
        }
    }

    private function fields()
    {
        return [
            Product::ENTITY => [
                'ox_featured' => [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Featured Product',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => Boolean::class,
                    'sort_order' => 3,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                ],
                'ox_custom' => [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Custom Label',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => Boolean::class,
                    'sort_order' => 4,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                ],
            ],
        ];
    }

}
