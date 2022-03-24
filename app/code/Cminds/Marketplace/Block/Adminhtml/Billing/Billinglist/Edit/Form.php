<?php

namespace Cminds\Marketplace\Block\Adminhtml\Billing\Billinglist\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Sales\Model\OrderFactory;

class Form extends Generic
{
    /**
     * Order factory object.
     *
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * Form constructor.
     *
     * @param Context      $context
     * @param Registry     $registry
     * @param FormFactory  $formFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OrderFactory $orderFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory
        );

        $this->orderFactory = $orderFactory;
    }

    protected function _prepareForm() // @codingStandardsIgnoreLine
    {
        $payment = $this->_coreRegistry
            ->registry('marketplacepaypal_billing_payment');

        $orderId = $payment->getOrderId();
        $supplierId = $payment->getSupplierId();

        $order = $this->orderFactory->create()
            ->load($orderId);

        $vendorIncome = $payment->getTotalVendorIncome();
        $paidAmount = $payment->getTotalPaidAmount();
        $unsettledAmount = $vendorIncome - $paidAmount;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        '*/*/save'
                    ),
                    'method' => 'post',
                ],
            ]
        );
        $form->setUseContainer(true);

        $fieldset = $form->addFieldset(
            'payment_form',
            [
                'legend' => __('Manual Payment Details'),
            ]
        );

        $fieldset->addField(
            'id',
            'hidden',
            [
                'name' => 'order_id',
                'value' => $orderId,
            ]
        );
        $fieldset->addField(
            'supplier_id',
            'hidden',
            [
                'name' => 'supplier_id',
                'value' => $supplierId,
            ]
        );
        $fieldset->addField(
            'order_id',
            'link',
            [
                'name' => 'order_id',
                'label' => __('Order Id'),
                'title' => __('Order Id'),
                'value' => __('#%1', $order->getRealOrderId()),
                'href' => $this->getUrl(
                    'sales/order/view',
                    ['order_id' => $order->getId()]
                ),
                'style' => 'font-weight:600;line-height:32px;',
            ]
        );
        $fieldset->addField(
            'paid',
            'text',
            [
                'name' => 'paid',
                'label' => __('Paid Amount'),
                'title' => __('Paid Amount'),
                'value' => $paidAmount,
                'disabled' => true,
            ]
        );
        $fieldset->addField(
            'unsettled',
            'text',
            [
                'name' => 'unsettled',
                'label' => __('Unsettled Amount'),
                'title' => __('Unsettled Amount'),
                'value' => $unsettledAmount,
                'disabled' => true,
            ]
        );
        $fieldset->addField(
            'amount',
            'text',
            [
                'label' => __('Amount To Pay'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'amount',
                'value' => $unsettledAmount,
            ]
        );
        $fieldset->addField(
            'payment_date',
            'date',
            [
                'label' => __('Payment Date'),
                'class' => 'required-entry',
                'required' => true,
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'name' => 'payment_date',
                'date_format' => $this->_localeDate
                    ->getDateFormat(\IntlDateFormatter::SHORT),
                'value' => $this->_localeDate->formatDate(
                    $this->_localeDate->date(),
                    \IntlDateFormatter::SHORT
                ),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
