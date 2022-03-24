<?php

namespace Cminds\Marketplace\Block\Settings;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\Fields;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Profile extends Template
{
    private $registry;
    private $fields;
    private $customerSession;
    private $customer;
    private $marketplaceHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        CustomerSession $customerSession,
        Customer $customer,
        MarketplaceHelper $marketplaceHelper,
        Fields $fields
    ) {
        parent::__construct($context);

        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->fields = $fields;
    }

    public function getCustomer()
    {
        return $this->registry->registry('customer');
    }

    public function getCustomFieldsValues($skipSystem = false, $new = false)
    {
        $customer = $this->getCustomer();
        $dbValues = unserialize($customer->getCustomFieldsValues()) ?: [];
        $ret = [];

        if($dbValues){
            foreach ($dbValues as $value) {
                $v = $this->fields->load($value['name'], 'name');
                if ($skipSystem) {
                    if ($v->getData('is_system')) {
                        continue;
                    }
                }

                if (isset($v)) {
                    $ret[] = $value;
                }
            }
        }
            

        return $ret;
    }

    public function getNewCustomFieldsValues($skipSystem = false)
    {
        $customer = $this->getCustomer();

        $dbValues = unserialize($customer->getNewCustomFieldsValues()) ?: [];
        $ret = [];

        foreach ($dbValues as $value) {
            $v = $this->fields->load($value['name'], 'name');
            if ($skipSystem) {
                if ($v->getData('is_system')) {
                    continue;
                }
            }

            if (isset($v)) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    public function getCustomFields($skipSystem = false)
    {
        $collection = $this->fields->getCollection();
        if ($skipSystem) {
            $collection->addFieldToFilter('is_system', 0);
        }

        return $collection;
    }

    public function getCustomField($field, $data = null)
    {
        $fieldHtml = '';

        switch ($field->getType()) {
            case 'text':
                $fieldHtml = $this->getTextField($field, $data);
                break;
            case 'textarea':
                $fieldHtml = $this->getTextareaField($field, $data);
                break;
            case 'date':
                $fieldHtml = $this->getDateField($field, $data);
                break;
        }

        return $fieldHtml;
    }

    private function getTextField($attribute, $data)
    {
        $value = $this->getValue($data, $attribute->getName());
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="' . $value
            . '" name="' . $attribute->getName()
            . '" id="' . $attribute->getName()
            . '" class="input-text form-control' . $class . '">';
    }

    private function getTextareaField($attribute, $data)
    {
        $value = $this->getValue($data, $attribute->getName());
        $class = $attribute->getIsRequired() ? ' required' : '';
        $class .= $attribute->getIsWysiwyg() ? ' wysiwyg' : '';

        return '<textarea name="' . $attribute->getName()
            . '" id="' . $attribute->getName()
            . '" class="input-text form-control' . $class
            . '"">' . $value . '</textarea>';
    }

    private function getDateField($attribute, $data)
    {
        $value = $this->getValue($data, $attribute->getName());
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="' . $value . '" name="'
            . $attribute->getName() . '" id="' . $attribute->getName()
            . '" value="' . $value
            . '" class="datepicker input-text form-control' . $class . '">';
    }

    private function getValue($data, $customFieldName)
    {
        if (!is_array($data)) {
            return '';
        }

        foreach ($data as $value) {
            if ($customFieldName == $value['name']) {
                return $value['value'];
            }
        }

        return '';
    }

    public function getFieldLabel($name)
    {
        $label = $this->fields->load($name, 'name')->getData('label');

        return $label;
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    public function getCustomerModel()
    {
        return $this->customer;
    }

    public function getMarketplaceHelper()
    {
        return $this->marketplaceHelper;
    }
}
