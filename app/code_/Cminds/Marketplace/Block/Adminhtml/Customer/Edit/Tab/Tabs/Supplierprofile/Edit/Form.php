<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs\Supplierprofile\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Config\Model\Config\Source\YesnoFactory;
use Cminds\Marketplace\Helper\Data;
use Cminds\Marketplace\Model\Fields;

class Form extends Generic
{
    protected $_registry;
    protected $_yesnoFactory;
    protected $_fields;
    protected $_helper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        YesnoFactory $yesnoFactory,
        Data $helper,
        Fields $fields
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory
        );

        $this->_registry = $registry;
        $this->_yesnoFactory = $yesnoFactory;
        $this->_helper = $helper;
        $this->_fields = $fields;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('marketplace/customer/tab/view/profile.phtml');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $changedDataExists = false;
        $customer = $this->_registry->registry('current_customer');
        $customFieldsValues = $this->getCustomFieldsValues(true, true);

        if( $customer->getData('supplier_name_new') 
            || $customer->getData('supplier_description_new') 
            || count( $customFieldsValues ) 
        )
            $changedDataExists = true;

        $fieldset = $form->addFieldset(
            'customer_profile_data_new',
            []
        );

        $supplierNameNew = '';
        $supplierDescriptionNew = '';
        if( $changedDataExists ) {
            $supplierNameNew = $customer->getData('supplier_name_new') ? 
                $customer->getData('supplier_name_new') : $customer->getData('supplier_name');
            $supplierDescriptionNew = $customer->getData('supplier_description_new') ? 
                $customer->getData('supplier_description_new') : $customer->getData('supplier_description');
        }

        $fieldset->addField(
            'supplier_profile_name_new',
            'text',
            [
                'label' => __('Name'),
                'name' => 'supplier_name_new',
                'data-form-part' => "customer_form",
                'value' => $supplierNameNew,
            ]
        );
        $fieldset->addField(
            'supplier_profile_description_new',
            'textarea',
            [
                'label' => __('Description'),
                'name' => 'supplier_description_new',
                'value' => $supplierDescriptionNew,
                'wysiwyg' => true,
                'data-form-part' => "customer_form",
            ]
        );

        $customFieldsCollection = $this->_fields->getCollection();
        $customFieldsValues = $this->getNewCustomFieldsValues(true);
        foreach ($customFieldsCollection AS $customField) {
            $fieldConfig['label'] = __($customField->getLabel());
            $fieldConfig['data-form-part'] = "customer_form";
            $fieldConfig['name'] = $customField->getName() . '_new';
            $fieldConfig['value'] = $this
                ->_findValue(
                    $customField->getName(),
                    $customFieldsValues
                );

            if ($customField->getType() == 'textarea'
                && $customField->getWysiwyg()
            ) {
                $fieldConfig['wysiwyg'] = true;
            }

            if ($customField->getType() == 'date') {
                $fieldConfig['date_format'] = 'M/d/Y';
                $fieldConfig['time_format'] = '';
            }

            $fieldset->addField(
                $customField->getName() . '_new',
                $customField->getType(),
                $fieldConfig
            );
        }

        return parent::_prepareForm();
    }

    private function _findValue($name, $data)
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data AS $value) {
            if ($value['name'] == $name) {
                return $value['value'];
            }
        }

        return false;
    }

    public function getRegistry($param)
    {
        return $this->_registry->registry($param);
    }

    public function getHelper()
    {
        return $this->_helper;
    }

    public function getCustomFieldsValues($skipSystem = false, $new = false)
    {
        $customer = $this->getRegistry('current_customer');
        if( $new )
            $dbValues = unserialize($customer->getNewCustomFieldsValues());
        else 
            $dbValues = unserialize($customer->getCustomFieldsValues());
        $ret = [];

        if (!$dbValues) {
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

    public function getNewCustomFieldsValues($skipSystem = false)
    {
        $customer = $this->getRegistry('current_customer');

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

    public function getFieldLabel($name)
    {
        $label = $this->_fields->load($name, 'name')->getData('label');

        return $label;
    }
}
