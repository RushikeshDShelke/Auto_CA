<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Customer;

use Magento\Customer\Model\Group as CustomerGroup;

class Group
{
    protected $_group;

    public function __construct(CustomerGroup $group)
    {
        $this->_group = $group;
    }

    public function toOptionArray()
    {
        $customer_group = $this->_group;
        $allGroups = $customer_group->getCollection();
        $allSet = [];

        foreach ($allGroups as $attributeSet) {
            $allSet[] = [
                'value' => $attributeSet->getCustomerGroupId(),
                'label' => $attributeSet->getCustomerGroupCode(),
            ];
        }

        return $allSet;
    }
}
