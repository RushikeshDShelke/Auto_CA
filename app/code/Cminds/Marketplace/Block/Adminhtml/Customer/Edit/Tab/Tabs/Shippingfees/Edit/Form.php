<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs\Shippingfees\Edit;

use Cminds\Marketplace\Model\ResourceModel\Methods\CollectionFactory as MethodsCollectionFactory;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

/**
 * Class Form
 *
 * @package Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs\Shippingfees\Edit
 */
class Form extends Generic
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Template path.
     *
     * @var string
     */
    protected $_template = 'Cminds_Marketplace::widget/form.phtml';

    /**
     * @var MethodsCollectionFactory
     */
    private $methodsCollectionFactory;

    /**
     * Form constructor.
     *
     * @param Context                  $context
     * @param Registry                 $registry
     * @param FormFactory              $formFactory
     * @param MethodsCollectionFactory $methodsCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        MethodsCollectionFactory $methodsCollectionFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory
        );

        $this->registry = $registry;
        $this->methodsCollectionFactory = $methodsCollectionFactory;
    }

    /**
     * Prepare form.
     *
     * @return Form|Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $supplierId = $this->registry
            ->registry('current_customer')
            ->getData('entity_id');
        $supplierShippingMethods = $this->methodsCollectionFactory
            ->create()
            ->addFieldToFilter('supplier_id', $supplierId)
            ->getItems();

        $form = $this->_formFactory->create(
            array(
                'data' => array(
                    'id' => 'edit_form',
                    'method' => 'post',
                    'enctype'=>'multipart/form-data',
                ),
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        // Add new empty method
        $fieldset = $form->addFieldset(
            'method_id',
            array('legend' => __('New Method'))
        );

        $fieldset->addField(
            'method_name_',
            'text',
            array(
                'label' => __('Method Name'),
                'name' => 'method_name[]',
                'value' => '',
                'data-form-part' => 'customer_form',
                'note' => 'Leave empty if you don\'t want to add a new Method.'
            )
        );

        $fieldset->addField(
            'method_type_',
            'select',
            array(
                'label' => __('Method Type'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'method_type[]',
                'options' => array(
                    0 => __('--- Select Shipping Method ---'),
                    1 => __('Flat Rate'),
                    2 => __('Table Rate'),
                    3 => __('Free Shipping'),
                ),
                'value' => 0,
                'data-form-part' => 'customer_form',
            )
        );

        $fieldset->addField(
            'flatrate_fee_',
            'text',
            array(
                'label' => __('Flat Rate Handling Fee'),
                'name' => 'flatrate_fee[]',
                'value' => '',
                'data-form-part' => 'customer_form'
            )
        );

        $fieldset->addField(
            'tablerate_fee_',
            'text',
            array(
                'label' => __('Table Rate Fee'),
                'name' => 'tablerate_fee[]',
                'value' => '',
                'data-form-part' => 'customer_form',
            )
        );

        $fieldset->addField(
            'tablerate_condition_',
            'select',
            array(
                'label' => __('Condition'),
                'name' => 'tablerate_condition[]',
                'options' => array(
                    0 => __('--- Select Condition ---'),
                    1 => __('Weight vs. Destination'),
                    2 => __('Price vs. Destination'),
                    3 => __('# of Items vs. Destination'),
                ),
                'value' => 0,
                'data-form-part' => 'customer_form',
            )
        );

        $fieldset->addField(
            'tablerate_csv_file_0',
            'file',
            array(
                'label' => __('Upload CSV file'),
                'name' => 'tablerate_csv_file[0]',    // for upload file it needs to be with 0-index
                'data-form-part' => 'customer_form',
            )
        );
        $fieldset->addField(
            'methods_to_delete_',
            'hidden',
            array(
                'name' => 'methods_to_delete[]',
                'data-form-part' => 'customer_form',
            )
        );

        foreach ($supplierShippingMethods as $method) {
            $fieldset = $form->addFieldset(
                'method_id_' . $method->getData('id'),
                array('legend' => $method->getData('name'),)
            );

            $isFlatRateAvailable = (int)$method->getData('flat_rate_available');
            $isTableRateAvailable = (int)$method->getData('table_rate_available');
            $isFreeShippingAvailable = (int)$method->getData('free_shipping');

            if ($isFlatRateAvailable === 1) {
                $selectedMethod = 1;
            } elseif ($isTableRateAvailable === 1) {
                $selectedMethod = 2;
            } elseif ($isFreeShippingAvailable === 1) {
                $selectedMethod = 3;
            }

            $fieldset->addField(
                'method_name_' . $method->getData('id'),
                'text',
                array(
                    'label' => __('Method Name'),
                    'name' => 'method_name['. $method->getData('id') . ']',
                    'value' => $method->getData('name'),
                    'data-form-part' => 'customer_form',
                    'required' => true
                )
            );

            $fieldset->addField(
                'method_type_' . $method->getData('id'),
                'select',
                array(
                    'label' => __('Method Type'),
                    'class' => 'required-entry',
                    'required' => true,
                    'name' => 'method_type['. $method->getData('id') .']',
                    'options' => array(
                        1 => __('Flat Rate'),
                        2 => __('Table Rate'),
                        3 => __('Free Shipping'),
                    ),
                    'value' => $selectedMethod,
                    'data-form-part' => 'customer_form',
                )
            );

            $fieldset->addField(
                'flatrate_fee_' . $method->getData('id'),
                'text',
                array(
                    'label' => __('Flat Rate Handling Fee'),
                    'name' => 'flatrate_fee['. $method->getData('id') . ']',
                    'value' => $method->getData('flat_rate_fee'),
                    'data-form-part' => 'customer_form'
                )
            );

            $fieldset->addField(
                'tablerate_fee_' . $method->getData('id'),
                'text',
                array(
                    'label' => __('Table Rate Fee'),
                    'name' => 'tablerate_fee['. $method->getData('id') . ']',
                    'value' => $method->getData('table_rate_fee'),
                    'data-form-part' => 'customer_form',
                )
            );

            $fieldset->addField(
                'tablerate_condition_' . $method->getData('id'),
                'select',
                array(
                    'label' => __('Condition'),
                    'name' => 'tablerate_condition['. $method->getData('id') . ']',
                    'options' => array(
                        1 => __('Weight vs. Destination'),
                        2 => __('Price vs. Destination'),
                        3 => __('# of Items vs. Destination'),
                    ),
                    'value' => $method->getData('table_rate_condition'),
                    'data-form-part' => 'customer_form',
                )
            );

            $fieldset->addField(
                'tablerate_csv_file_' . $method->getData('id'),
                'file',
                array(
                    'label' => __('Upload CSV file'),
                    'name' => 'tablerate_csv_file['. $method->getData('id') . ']',
                    'data-form-part' => 'customer_form',
                )
            );

            $fieldset->addField(
                'delete_method_button_' . $method->getData('id'),
                'button',
                array(
                    'value' => __('Delete method'),
                    'name' => 'delete_method_button_' . $method->getData('id')
                )
            );

            $fieldset->addField(
                'methods_to_delete_' . $method->getData('id'),
                'hidden',
                array(
                    'name' => 'methods_to_delete[' . $method->getData('id') . ']',
                    'data-form-part' => 'customer_form',
                )
            );
        }

        return parent::_prepareForm();
    }
}
