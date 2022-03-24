<?php

namespace Kellton\CustomerGstpan\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;

class CustomerSetup extends EavSetup {

	protected $eavConfig;

	public function __construct(
		ModuleDataSetupInterface $setup,
		Context $context,
		CacheInterface $cache,
		CollectionFactory $attrGroupCollectionFactory,
		Config $eavConfig
		) {
		$this -> eavConfig = $eavConfig;
		parent :: __construct($setup, $context, $cache, $attrGroupCollectionFactory);
	}

	public function installAttributes($customerSetup) {
		$this -> installCustomerAttributes($customerSetup);
		$this -> installCustomerAddressAttributes($customerSetup);
	}

	public function installCustomerAttributes($customerSetup) {
			

		$customerSetup -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
			'customer_gst',
			[
			'label' => 'GSTN No',
			'system' => 0,
			'position' => 100,
            'sort_order' =>100,
            'visible' =>  false,
			'note' => '',
				

                        'type' => 'varchar',
                        'input' => 'text',
			
			]
			);

		$customerSetup -> getEavConfig() -> getAttribute('customer', 'customer_gst')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer', 'checkout_register', 'customer_account_create', 'customer_account_edit', 'adminhtml_checkout']) -> save();

				

		$customerSetup -> addAttribute(\Magento\Customer\Model\Customer::ENTITY,
			'customer_pan',
			[
			'label' => 'PAN No',
			'system' => 0,
			'position' => 200,
            'sort_order' =>200,
            'visible' =>  true,
			'note' => '',
				

                        'type' => 'varchar',
                        'input' => 'text',
			
			]
			);

		$customerSetup -> getEavConfig() -> getAttribute('customer', 'customer_pan')->setData('is_user_defined',1)->setData('is_required',0)->setData('default_value','')->setData('used_in_forms', ['adminhtml_customer', 'checkout_register', 'customer_account_create', 'customer_account_edit', 'adminhtml_checkout']) -> save();

				
	}

	public function installCustomerAddressAttributes($customerSetup) {
			
	}

	public function getEavConfig() {
		return $this -> eavConfig;
	}
} 
