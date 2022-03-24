<?php

namespace Wbcom\PincodeChecker\Block\Adminhtml\Pincode\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;

class Info extends Generic implements TabInterface{

    /**
     * @var Config
     */
    protected $_wysiwygConfig;
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * Info constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_coreSession = $coreSession;
        $this->_websiteFactory = $websiteFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('pincode');
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information')]
        );
        if($model->getId()){
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Is Active'),
                'title' => __('Is Active'),
                'class' => 'main_acount',
                'values' => [
                    ['label' => __('No'), 'value' => 'Unactive'],
                    ['label' => __('Yes'), 'value' => 'Active']
                ]
            ]
        );
        $fieldset->addField(
            'cod',
            'select',
            [
                'name' => 'cod',
                'label' => __('Cash On Delivery'),
                'title' => __('Cash On Delivery'),
                'class' => 'main_acount',
                'values' => [
                    ['label' => __('No'), 'value' => 'Undelivered'],
                    ['label' => __('Yes'), 'value' => 'Delivered']
                ]
            ]
        );
        $fieldset->addField(
            "pincode",
            'text',
            [
                'name' => 'pincode',
                'label' => __('Pincode'),
                'comment' => __('Pincode'),
                'required' => true
            ]
        );

        $fieldset->addField(
            "country_code",
            'text',
            [
                'name' => 'country_code',
                'label' => __('Country Code'),
                'comment' => __('Country Code'),
                'required' => true,
                'note' => __('Enter your country code like IN, US, AU etc.'),
            ]
        );

        $fieldset->addField(
            'delivery_days',
            'select',
            [
                'name' => 'delivery_days',
                'label' => __('Expected Delivery Days'),
                'title' => __('Expected Delivery Days'),
                'class' => 'main_acount',
                'values' => [
                    ['label' => __('1 to 2 Days'), 'value' => '1-to-2'],
                    ['label' => __('2 to 4 Days'), 'value' => '2-to-4'],
                    ['label' => __('4 to 6 Days'), 'value' => '4-to-6'],
                    ['label' => __('6 to 8 Days'), 'value' => '6-to-8'],
                    ['label' => __('8 to 10 Days'), 'value' => '8-to-10'],
                    ['label' => __('10 to 12 Days'), 'value' => '10-to-12'],
                    ['label' => __('12 to 14 Days'), 'value' => '12-to-14'],
                    ['label' => __('14 to 16 Days'), 'value' => '14-to-16']
                ]
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Pincode');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Pincode');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
