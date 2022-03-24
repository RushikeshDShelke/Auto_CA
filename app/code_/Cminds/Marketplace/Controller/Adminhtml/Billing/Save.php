<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Billing;

use Cminds\Marketplace\Model\PaymentFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * Payment factory object.
     *
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * Save constructor.
     *
     * @param Context        $context
     * @param PaymentFactory $paymentFactory
     */
    public function __construct(
        Context $context,
        PaymentFactory $paymentFactory
    ) {
        parent::__construct($context);

        $this->paymentFactory = $paymentFactory;
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        if (empty($post)) {
            $this->messageManager->addErrorMessage(
                __('There was an error during manual payment creation.')
            );

            return $this->_redirect('*/*/');
        }

        $payment = $this->paymentFactory->create();
        $payment
            ->setSupplierId($post['supplier_id'])
            ->setOrderId($post['order_id'])
            ->setAmount($post['amount'])
            ->setPaymentDate($post['payment_date']);

        try {
            $payment->save();

            $this->messageManager->addSuccessMessage(
                __('Manual payment has been successfully created.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('There was an error during manual payment creation.')
            );

            return $this->_redirect('*/*/edit');
        }

        return $this->_redirect('*/*/');
    }
}
