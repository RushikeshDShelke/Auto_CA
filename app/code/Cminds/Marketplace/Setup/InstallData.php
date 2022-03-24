<?php

namespace Cminds\Marketplace\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $customerSetupFactory;

    /**
     * Object constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory Customer setup factory object.
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup Module data setup object.
     * @param ModuleContextInterface   $context Module context object.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'percentage_fee',
            [
                'type' => 'int',
                'label' => __('Sales Percentage Fee'),
                'input' => 'text',
                'required' => false,
                'default' => 10,
                'visible' => true,
                'admin_only' => true,
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'percentage_fee'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_name',
            [
                'type' => 'text',
                'label' => 'Supplier Name',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'default' => '',
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_name'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_description',
            [
                'type' => 'text',
                'label' => 'Supplier Description',
                'input' => 'textarea',
                'visible' => false,
                'required' => false,
                'default' => '',
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_description'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_profile_visible',
            [
                'type' => 'int',
                'label' => 'Supplier Profile Visibility',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => false,
                'required' => false,
                'default' => 0,
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_profile_visible'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_profile_approved',
            [
                'type' => 'int',
                'label' => 'Supplier Profile Approved',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => false,
                'required' => false,
                'default' => 0,
                'admin_only' => true,
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_profile_approved'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_remark',
            [
                'type' => 'text',
                'label' => 'Comment',
                'input' => 'textarea',
                'visible' => false,
                'required' => false,
                'default' => '',
                'admin_only' => true,
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_remark'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'rejected_notfication_seen',
            [
                'type' => 'int',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'default' => 1,
                'admin_only' => true,
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'rejected_notfication_seen'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_name_new',
            [
                'type' => 'text',
                'label' => 'Supplier Name After Change',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'default' => '',
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_name_new'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_description_new',
            [
                'type' => 'text',
                'label' => 'Supplier Description After Change',
                'input' => 'textarea',
                'visible' => false,
                'required' => false,
                'default' => '',
                'system' => 0,
            ]
        );

        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_description_new'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'custom_fields_values',
            [
                'type' => 'text',
                'input' => 'textarea',
                'visible' => false,
                'required' => false,
                'default' => '',
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'custom_fields_values'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'new_custom_fields_values',
            [
                'type' => 'text',
                'input' => 'textarea',
                'visible' => false,
                'required' => false,
                'default' => '',
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'new_custom_fields_values'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'supplier_logo',
            [
                'type' => 'text',
                'label' => 'Supplier Logo file',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'default' => '',
                'adminhtml_only' => '1',
                'system' => 0,
            ]
        );
        $customerSetup
            ->getEavConfig()
            ->getAttribute(
                'customer',
                'supplier_logo'
            )
            ->setData('used_in_forms', ['adminhtml_customer'])
            ->save();

        $setup->endSetup();
    }
}
