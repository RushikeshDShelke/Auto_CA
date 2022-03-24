<?php

namespace Meetanshi\Mobilelogin\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class InstallData
 * @package Meetanshi\Mobilelogin\Setup
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeRepository $attributeRepository
    ) {
    
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // add customer_attribute to customer
        $eavSetup->removeAttribute(Customer::ENTITY, 'mobile_number');
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'mobile_number',
            [
                'type' => 'varchar',
                'label' => 'Mobile Number',
                'input' => 'text',
                'required' => false,
                'system' => 0,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'sort_order' => '2000',
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'visible_on_front' => true,
                'unique' => true,
            ]
        );

        // allow customer_attribute attribute to be saved in the specific areas
        $attribute = $this->attributeRepository->get('customer', 'mobile_number');
        $setup->getConnection()
            ->insertOnDuplicate(
                $setup->getTable('customer_form_attribute'),
                [
                    ['form_code' => 'adminhtml_customer', 'attribute_id' => $attribute->getId()],
                    ['form_code' => 'customer_account_create', 'attribute_id' => $attribute->getId()],
                    ['form_code' => 'customer_account_edit', 'attribute_id' => $attribute->getId()],
                ]
            );
    }
}
