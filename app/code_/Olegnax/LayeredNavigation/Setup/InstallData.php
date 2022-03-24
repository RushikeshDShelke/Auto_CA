<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;
    private $categorySetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, CategorySetupFactory $categorySetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
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
        $tablefields = $this->fields();
        if (!empty($tablefields)) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(Category::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
            foreach ($tablefields as $entity => $fields) {
                foreach ($fields as $key => $value) {
                    if (Category::ENTITY == $entity) {
                        $categorySetup->addAttribute($entity, $key, $value);
                        $group = $value['group'];
                        $idg = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId,
                            $group);
                        $categorySetup->addAttributeToGroup(
                            $entityTypeId, $attributeSetId, $idg, $key, $value['sort_order']
                        );
                    } else {
                        $eavSetup->addAttribute($entity, $key, $value);
                    }
                }
            }
        }
    }

    private function fields()
    {
        return [
            Category::ENTITY => [
                'ox_nav_disable_ajax' => [
                    'type' => 'int',
                    'label' => 'Disable AJAX in Layered Navigation',
                    'input' => 'select',
                    'source' => Boolean::class,
                    'required' => false,
                    'sort_order' => 10,
                    'group' => 'Display Settings',
                    'default' => 0,
                ],
            ],
        ];
    }

}
