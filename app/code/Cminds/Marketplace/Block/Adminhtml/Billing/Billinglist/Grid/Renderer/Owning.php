<?php

namespace Cminds\Marketplace\Block\Adminhtml\Billing\Billinglist\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;

class Owning extends AbstractRenderer
{
    private $currencyHelper;

    public function __construct(
        Context $context,
        CurrencyHelper $currencyHelper
    ) {
        parent::__construct($context);

        $this->currencyHelper = $currencyHelper;
    }

    public function render(DataObject $row)
    {
        $totalIncome = $row->getData('total_vendor_income');
        $totalPaid = $row->getData('total_paid_amount');

        $owning = $totalIncome - $totalPaid;

        if ((float)$owning <= 0.0) {
            $owning *= -1;

            $value = $this->currencyHelper->currency(
                $owning,
                true,
                false
            );

            return '<div style="color:#FFF;font-weight:bold;background:green;'
            . 'border-radius:8px;padding:2px 6px;">'
            . $value . '</div>';
        } else {
            $value = $this->currencyHelper->currency(
                $owning,
                true,
                false
            );

            return '<div style="color:#FFF;font-weight:bold;background:red;'
            . 'border-radius:8px;padding:2px 6px;">' . $value . '</div>';
        }
    }
}
