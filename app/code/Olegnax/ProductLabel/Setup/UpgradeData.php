<?php


namespace Olegnax\ProductLabel\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
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
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $methods = $this->getUpgradeFunctions($context->getVersion());
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        foreach ($methods as $method) {
            call_user_func_array([$this, $method], [$setup, $eavSetup]);
        }
    }

    private function getUpgradeFunctions($low_version = '1.0.0')
    {
        $methods = get_class_methods($this);
        foreach ($methods as $key => $method) {
            if (preg_match('/^upgrade_(.+)$/i', $method, $matches)) {
                if (version_compare($low_version, $matches[1], '<')) {
                    continue;
                }
            }
            unset($methods[$key]);
        }

        $methods = array_filter($methods);
        $methods = array_unique($methods);
        sort($methods);

        return $methods;
    }

    public function upgrade_0_0_11(
        ModuleDataSetupInterface $setup,
        EavSetup $eavSetup
    ) {
        $eavSetup->addAttribute(
            Product::ENTITY,
            'ox_custom',
            [
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
            ]
        );
    }
}