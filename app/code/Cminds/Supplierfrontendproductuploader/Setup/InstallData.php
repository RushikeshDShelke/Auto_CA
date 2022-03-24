<?php

namespace Cminds\Supplierfrontendproductuploader\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSetModel;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
    private $categoryCollectionFactory;
    private $customerSetupFactory;
    private $groupFactory;
    private $resourceConfig;
    private $product;
    private $attributeSetModel;
    private $moduleDataSetupInterface;
    private $appState;
    private $coreRegistry;

    /**
     * Object constructor.
     *
     * @param EavSetupFactory          $eavSetupFactory           Eav setup factory object.
     * @param GroupFactory             $groupFactory              Group factory object.
     * @param ResourceConfig           $resourceConfig            Resource config object.
     * @param Product                  $product                   Product object.
     * @param AttributeSetModel        $attributeSet              Attribute set object.
     * @param ModuleDataSetupInterface $moduleDataSetupInterface  Module data setup object.
     * @param CollectionFactory        $categoryCollectionFactory Category collection factory object.
     * @param CustomerSetupFactory     $customerSetupFactory      Customer setup factory object.
     * @param AppState                 $appState
     * @param CoreRegistry $coreRegistry
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        GroupFactory $groupFactory,
        ResourceConfig $resourceConfig,
        Product $product,
        AttributeSetModel $attributeSet,
        ModuleDataSetupInterface $moduleDataSetupInterface,
        CollectionFactory $categoryCollectionFactory,
        CustomerSetupFactory $customerSetupFactory,
        AppState $appState,
        CoreRegistry $coreRegistry
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->groupFactory = $groupFactory;
        $this->resourceConfig = $resourceConfig;
        $this->product = $product;
        $this->attributeSetModel = $attributeSet;
        $this->moduleDataSetupInterface = $moduleDataSetupInterface;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->appState = $appState;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup   Module data setup object.
     * @param ModuleContextInterface   $context Module context object.
     *
     * @return void
     * @throws \Exception
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Category::ENTITY,
            'available_for_supplier',
            [
                'group' => 'General',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Available for Supplier?',
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'default' => 1,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'creator_id',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'input' => 'select',
                'source' => 'Cminds\Supplierfrontendproductuploader\Model\Source\Suppliers',
                'label' => 'Supplier',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => 1,
            ]
        );

        $customerGroup = $this->groupFactory->create();
        $customerGroup->load('Supplier Pro', 'customer_group_code');

        if (!$customerGroup->getId()) {
            $customerGroup
                ->setCode('Supplier Pro')
                ->setTaxClassId(3)
                ->save();
        }

        $this->resourceConfig->saveConfig(
            'configuration/suppliers_group'
            . '/suppliert_group_which_can_edit_own_products',
            $customerGroup->getId(),
            'default',
            0
        );

        $setName = 'Supplier Default';
        $entityAttributeSetModel = $this->attributeSetModel;
        $entityTypeId = $this->product->getResource()->getTypeId();

        $attributeSetId = $eavSetup->getAttributeSet(
            $entityTypeId,
            $setName,
            'attribute_set_id'
        );

        if (!$attributeSetId) {
            $skeletonId = $eavSetup->getAttributeSet(
                $entityTypeId,
                'Default',
                'attribute_set_id'
            );
            $entityAttributeSetModel->setEntityTypeId($entityTypeId);
            $entityAttributeSetModel->setAttributeSetName($setName);
            $entityAttributeSetModel->setAvailableForSupplier(1);
            $entityAttributeSetModel->save();
            $entityAttributeSetModel->initFromSkeleton($skeletonId);
            $entityAttributeSetModel->save();
            $newSetId = $entityAttributeSetModel->getId();
        } else {
            $newSetId = $attributeSetId;
        }

        $this->moduleDataSetupInterface->deleteTableRow(
            'eav_entity_attribute',
            'attribute_id',
            $eavSetup->getAttributeId(
                'catalog_product',
                'product_construction_source'
            ),
            'attribute_set_id',
            $eavSetup->getAttributeSetId('catalog_product', 'Default')
        );

        $coreConfig = $this->resourceConfig;
        $coreConfig->saveConfig(
            'products_settings/adding_products/attributes_set',
            $newSetId,
            'default',
            0
        );

        $supplierGroup = $this->groupFactory->create();
        $supplierGroup->load('Supplier', 'customer_group_code');

        if (!$supplierGroup->getId()) {
            $supplierGroup
                ->setCode('Supplier')
                ->setTaxClassId(3)
                ->save();
        }

        $coreConfig = $this->resourceConfig;
        $coreConfig->saveConfig(
            'configuration/suppliers_group/supplier_group',
            $supplierGroup->getId(),
            'default',
            0
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'admin_product_note',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'input' => 'textarea',
                'label' => 'Remark',
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'supplier_actived_product',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => '',
                'frontend_input' => 'text',
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => 1,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'frontendproduct_product_status',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Status Set by Supplier',
                'input' => 'select',
                'source' => 'Cminds\Supplierfrontendproductuploader'
                    . '\Model\Source\Approved',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 1,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => 1,
            ]
        );

        $eavSetup->addAttribute(
            Customer::ENTITY,
            'notification_product_ordered',
            [
                'type' => 'int',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'input' => 'select',
                'label' => 'Send notification when product was ordered',
                'visible' => true,
                'required' => false,
                'default' => 1,
                'admin_only' => true,
                'system' => 0,
            ]
        );

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->getEavConfig()
            ->getAttribute(
                'customer',
                'notification_product_ordered'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $eavSetup->addAttribute(
            Customer::ENTITY,
            'notification_product_approved',
            [
                'type' => 'int',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'input' => 'select',
                'label' => 'Send notification when product was approved',
                'visible' => true,
                'required' => false,
                'default' => 1,
                'admin_only' => true,
                'system' => 0,
            ]
        );

        $customerSetup->getEavConfig()
            ->getAttribute(
                'customer',
                'notification_product_approved'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_approve',
            [
                'type' => 'int',
                'label' => 'Is Supplier Approved',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => true,
                'required' => false,
                'default' => 0,
                'admin_only' => true,
                'system' => 0,
            ]
        );
        $customerSetup->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_approve'
            )
            ->setData(
                'used_in_forms',
                ['adminhtml_customer']
            )
            ->save();

        $setup->endSetup();
    }
}
