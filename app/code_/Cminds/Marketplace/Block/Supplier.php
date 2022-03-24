<?php

namespace Cminds\Marketplace\Block;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\Fields;
use Magento\Customer\Model\Customer;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Supplier extends Template
{
    /**
     * Registry Object.
     *
     * @var Registry
     */
    protected $_registry;

    /**
     * Fields Collection.
     *
     * @var Fields
     */
    protected $_fields;

    /**
     * Marketplace Helper.
     *
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;

    /**
     * Supplier constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Fields $fields
     * @param MarketplaceHelper $marketplaceHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Fields $fields,
        MarketplaceHelper $marketplaceHelper
    ) {
        parent::__construct($context);

        $this->_registry = $registry;
        $this->_fields = $fields;
        $this->_marketplaceHelper = $marketplaceHelper;
    }

    /**
     * Get current logged in customer.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->_registry->registry('customer');
    }

    /**
     * Get values of custom fields, which are approved.
     *
     * @param bool $skipSystem
     *
     * @return array
     */
    public function getCustomFieldsValues($skipSystem = false)
    {
        $customer = $this->getCustomer();

        $dbValues = unserialize($customer->getCustomFieldsValues());
        $ret = [];

        if (!$customer->getCustomFieldsValues()) {
            return $ret;
        }

        foreach ($dbValues AS $value) {
            $v = $this->_fields->load($value['name'], 'name');
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

    /**
     * Get fields of values, which are not approved.
     *
     * @param bool $skipSystem
     *
     * @return array
     */
    public function getNewCustomFieldsValues($skipSystem = false)
    {
        $customer = $this->getCustomer();

        $dbValues = unserialize($customer->getNewCustomFieldsValues());
        $ret = [];

        if (!$customer->getNewCustomFieldsValues()) {
            return $ret;
        }

        foreach ($dbValues AS $value) {
            $v = $this->_fields->load($value['name'], 'name');
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

    /**
     * Get Custom Fields Collection.
     *
     * @return AbstractCollection
     */
    public function getCustomFields()
    {
        $collection = $this->_fields->getCollection();

        return $collection;
    }

    /**
     * Get custom field html.
     *
     * @param $field
     * @param null $data
     *
     * @return string
     */
    public function getCustomField($field, $data = null)
    {
        switch ($field->getType()) {
            case 'text' :
                return $this->_getTextField($field, $data);
                break;
            case 'textarea' :
                return $this->_getTextareaField($field, $data);
                break;
            case 'date' :
                return $this->_getDateField($field, $data);
                break;
            default :
                return '';
                break;
        }
    }

    /**
     * Get text field html.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    private function _getTextField($attribute, $data)
    {
        $value = $this->_getValue($data, $attribute->getName());
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="' . $value . '" name="'
        . $attribute->getName() . '" id="' . $attribute->getName()
        . '" class="input-text form-control' . $class . '">';
    }

    /**
     * Get textarea html field.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    private function _getTextareaField($attribute, $data)
    {
        $value = $this->_getValue($data, $attribute->getName());
        $class = $attribute->getIsRequired() ? ' required' : '';
        $class .= $attribute->getIsWysiwyg() ? ' wysiwyg' : '';

        return '<textarea name="' . $attribute->getName() . '" id="'
        . $attribute->getName() . '" class="input-text form-control'
        . $class . '"">' . $value . '</textarea>';
    }

    /**
     * Get date html field.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    private function _getDateField($attribute, $data)
    {
        $value = $this->_getValue($data, $attribute->getName());
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="' . $value . '" name="'
        . $attribute->getName() . '" id="' . $attribute->getName()
        . '" value="' . $value
        . '" class="datepicker input-text form-control' . $class . '">';
    }

    /**
     * Get value of supplier's custom field.
     *
     * @param $data
     * @param $customFieldName
     *
     * @return string
     */
    private function _getValue($data, $customFieldName)
    {
        if (!is_array($data)) {
            return '';
        }

        foreach ($data AS $value) {
            if ($customFieldName == $value['name']) {
                return $value['value'];
            }
        }

        return '';
    }

    /**
     * Get field value.
     *
     * @param $name
     *
     * @return string
     */
    public function getFieldLabel($name)
    {
        $label = $this->_fields->load($name, 'name')->getData('label');

        return $label;
    }

    /**
     * Get Marketplace Helper.
     *
     * @return MarketplaceHelper
     */
    public function getMarketplaceHelper()
    {
        return $this->_marketplaceHelper;
    }

    /**
     * Get specific parameter from the request object.
     *
     * @param $key
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function getParam($key, $defaultValue = null)
    {
        return $this->_request->getParam($key, $defaultValue);
    }
}
