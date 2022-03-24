<?php

namespace Cminds\Marketplace\Block\Adminhtml\Billing\Billinglist\Grid\Renderer;

use Cminds\Marketplace\Model\Payment;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;

class Owningdiscount extends AbstractRenderer
{
    private $payment;
    private $currencyHelper;

    public function __construct(
        Context $context,
        Payment $payments,
        CurrencyHelper $currencyHelper
    ) {
        parent::__construct($context);

        $this->payment = $payments;
        $this->currencyHelper = $currencyHelper;
    }

    public function render(DataObject $row)
    {
        /*$orderId = $row->getData('order_id');
        $supplierId = $row->getData('supplier_id');
        $toPaid = $row->getData('vendor_amount_with_discount');

        $col = $this->payment->getCollection()
            ->addFilter(
                'order_id',
                $orderId
            )
            ->addFilter('supplier_id', $supplierId)
            ->getFirstItem();

        $owning = $toPaid - $col->getAmount();
        if ($owning === 0) {
            $value = $this->currencyHelper->currency(
                $toPaid - $col->getAmount(),
                true,
                false
            );

            return '<div style="color:#FFF;font-weight:bold;background:green;'
            . 'border-radius:8px;">' . $value . '</div>';
        } else {
            $value = $this->currencyHelper->currency(
                $toPaid - $col->getAmount(),
                true,
                false
            );

            return '<div style="color:#FFF;font-weight:bold;background:red;'
            . 'border-radius:8px;">' . $value . '</div>';
        }*/

        return '';
    }
}
