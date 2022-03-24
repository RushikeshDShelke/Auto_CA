<?php

namespace Cminds\Marketplace\Block\Adminhtml\Supplier\Customfields\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

class Form extends Generic
{
    protected $_registry;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory
        );

        $this->_registry = $registry;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        '*/*/editCustomField',
                        ['_current' => true, 'continue' => 0,]
                    ),
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);
        $fieldset = $form->addFieldset(
            'general',
            [
                'legend' => __('Details'),
            ]
        );


        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'name' => [
                    'label' => __('Name'),
                    'input' => 'text',
                    'required' => true,
                    'class' => 'validate-code',
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'label' => [
                    'label' => __('Label'),
                    'input' => 'text',
                    'required' => true,
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'description' => [
                    'label' => __('Description'),
                    'input' => 'textarea',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'options',
            [
                'legend' => __('Options'),
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'is_required' => [
                    'label' => __('Required'),
                    'input' => 'select',
                    'required' => true,
                    'values' => [
                        0 => 'No',
                        1 => 'Yes',
                    ],
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'is_system' => [
                    'label' => __('Is System'),
                    'input' => 'select',
                    'required' => true,
                    'values' => [
                        0 => 'No',
                        1 => 'Yes',
                    ],
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'type' => [
                    'label' => __('Type'),
                    'input' => 'select',
                    'required' => true,
                    'values' => [
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'date' => 'Date'
                    ],
                    'note' => __('Textarea can be wysiwyg')
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'is_wysiwyg' => [
                    'label' => __('Wysiwyg'),
                    'input' => 'select',
                    'required' => true,
                    'values' => [
                        0 => 'No',
                        1 => 'Yes',
                    ]
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'must_be_approved' => [
                    'label' => __('Must be approved'),
                    'input' => 'select',
                    'required' => true,
                    'values' => [
                        0 => 'No',
                        1 => 'Yes',
                    ],
                ],
            ]
        );

        $this->_addFieldsToFieldset(
            $fieldset,
            [
                'visible_on_create_form' => [
                    'label' => __('Can be visible on the supplier create form'),
                    'input' => 'select',
                    'required' => true,
                    'values' => [
                        0 => __('No'),
                        1 => __('Yes'),
                    ],
                ],
            ]
        );

        //Make 'is_wysiwyg' field dependant on custom supplier profile field type.
        //Display 'is_wysiwyg' drop-down if custom supplier profile field type is textarea, don't display if another.
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )->addFieldMap(
                'is_wysiwyg',
                'wysiwyg'
            )->addFieldMap(
                'type',
                'field_type'
            )->addFieldDependence(
                'wysiwyg',
                'field_type',
                'textarea'
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _addFieldsToFieldset(Fieldset $fieldset, $fields)
    {
        $requestData = new DataObject($this->getRequest()->getParams());

        foreach ($fields as $name => $_data) {
            $requestValue = $requestData->getData($name);
            if ($requestValue) {
                $_data['value'] = $requestValue;
            }

            $_data['name'] = "fieldData[$name]";
            $_data['title'] = $_data['label'];

            if (!array_key_exists('value', $_data)) {
                if ($this->_getField()) {
                    $_data['value'] = $this->_getField()->getData($name);
                }
            }

            $fieldset->addField($name, $_data['input'], $_data);
        }

        return $this;
    }

    protected function _getField()
    {
        if (!$this->hasData('field')) {
            $data = $this->_registry->registry('current_field');

            $this->setData('field', $data);
        }

        if (!$this->hasData('field')) {
            $data = $this->_registry->registry('current_field_post_data');
            $this->setData('field', $data);
        }

        return $this->getData('field');
    }
}
