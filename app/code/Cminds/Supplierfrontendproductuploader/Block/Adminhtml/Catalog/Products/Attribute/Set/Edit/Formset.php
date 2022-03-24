<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Catalog\Products\Attribute\Set\Edit;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset as MainFormset;

class Formset extends MainFormset
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_setFactory;

    protected $_yesnoFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context          $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\Data\FormFactory              $formFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory   $setFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
    ) {
        $this->_yesnoFactory = $yesnoFactory;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $setFactory
        );
    }

    /**
     * Prepares attribute set form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        $data = $this->_setFactory->create()
            ->load($this->getRequest()->getParam('id'));

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset(
            'set_name',
            ['legend' => __('Edit Attribute Set Name')]
        );
        $fieldset->addField(
            'attribute_set_name',
            'text',
            [
                'label' => __('Name'),
                'note' => __('For internal use'),
                'name' => 'attribute_set_name',
                'required' => true,
                'class' => 'required-entry',
                'value' => $data->getAttributeSetName(),
            ]
        );

        $yesno = $this->_yesnoFactory->create()->toArray();

        $fieldset->addField(
            'available_for_supplier',
            'select',
            [
                'name' => 'available_for_supplier',
                'label' => __('Available for Supplier'),
                'title' => __('Available for Supplier'),
                'values' => $yesno,
                'required' => true,
                'class' => 'required-entry',
                'value' => $data->getData('available_for_supplier'),
            ]
        );

        if (!$this->getRequest()->getParam('id', false)) {
            $fieldset->addField(
                'gotoEdit',
                'hidden',
                ['name' => 'gotoEdit', 'value' => '1']
            );

            $sets = $this->_setFactory->create()
                ->getResourceCollection()
                ->setEntityTypeFilter(
                    $this->_coreRegistry->registry('entityType')
                )
                ->load()
                ->toOptionArray();

            $fieldset->addField(
                'skeleton_set',
                'select',
                [
                    'label' => __('Based On'),
                    'name' => 'skeleton_set',
                    'required' => true,
                    'class' => 'required-entry',
                    'values' => $sets,
                ]
            );
        }

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('set-prop-form');
        $form->setAction($this->getUrl('catalog/*/save'));
        $form->setOnsubmit('return false;');
        $this->setForm($form);
    }
}
