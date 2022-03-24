<?php

namespace Olegnax\Athlete2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface {

	private $eavSetupFactory;

	/**
	 * Constructor
	 *
	 * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
	 */
	public function __construct(EavSetupFactory $eavSetupFactory) {
		$this->eavSetupFactory = $eavSetupFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function install(
	ModuleDataSetupInterface $setup, ModuleContextInterface $context
	) {
		$setup->startSetup();
		$this->_install($setup);
		$setup->endSetup();
	}

	private function fields() {
		return [
			\Magento\Catalog\Model\Product::ENTITY => [
				'ox_featured' => [
					'group' => 'Product Details',
					'type' => 'int',
					'backend' => '',
					'frontend' => '',
					'label' => 'Featured Product',
					'input' => 'boolean',
					'class' => '',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'sort_order' => 3,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
					'apply_to' => ''
				],
				'img_hover' => [
					'group' => 'Image Management',
					'type' => 'varchar',
					'label' => 'Hover Image',
					'input' => 'media_image',
					'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'user_defined' => false,
					'filterable' => false,
					'visible_on_front' => false,
					'used_in_product_listing' => true,
					'sort_order' => 10,
					'required' => false
				],
			],
		];
	}

	private function _install(ModuleDataSetupInterface $setup) {
		$eavSetup = $this->eavSetupFactory->create([
			'setup' => $setup]);
		$tablefields = $this->fields();
		foreach ($tablefields as $entity => $fields) {
			foreach ($fields as $key => $value) {
				$eavSetup->addAttribute($entity, $key, $value);
			}
		}
	}

}
