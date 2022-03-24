<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Register;

use Magento\Customer\Model\GroupFactory as CustomerGroupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

class Groups
{
    protected $_group;
    protected $_scopeConfig;

    public function __construct(
        CustomerGroupFactory $group,
        ScopeConfig $scopeConfig
    ) {
        $this->_group = $group;
        $this->_scopeConfig = $scopeConfig;
    }

    public function toOptionArray()
    {
        $supplierPro = $this->_scopeConfig
            ->getValue('configuration/suppliers_group/supplier_group');
        $supplier = $this->_scopeConfig
            ->getValue(
                'configuration/suppliers_group'
                . '/suppliert_group_which_can_edit_own_products'
            );

        $supplierProModel = $this->_group->create()->load($supplierPro);
        $supplierModel = $this->_group->create()->load($supplier);

        $config = [
            [
                'value' => $supplierProModel->getCustomerGroupId(),
                'label' => $supplierProModel->getCustomerGroupCode(),
            ],
            [
                'value' => $supplierModel->getCustomerGroupId(),
                'label' => $supplierModel->getCustomerGroupCode(),
            ],
        ];

        return $config;
    }
}
