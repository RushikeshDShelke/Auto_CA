<?php

namespace Cminds\Supplierfrontendproductuploader\Observer;

use Magento\Config\Model\Config\Source\YesnoFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class EavAttributeEditFormInit implements ObserverInterface
{
    /**
     * @var YesnoFactory
     */
    private $yesnoFactory;

    public function __construct(
        YesnoFactory $yesnoFactory
    ) {
        $this->yesnoFactory = $yesnoFactory;
    }

    public function execute(Observer $observer)
    {
        $fieldset = $observer->getForm()->getElement('base_fieldset');

        $yesno = $this->yesnoFactory->create()->toOptionArray();

        $fieldset->addField(
            'available_for_supplier',
            'select',
            [
                'name' => 'available_for_supplier',
                'label' => __('Visible for Supplier'),
                'title' => __('Visible for Supplier'),
                'values' => $yesno,
            ]
        );
    }
}
