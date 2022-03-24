<?php

namespace Cminds\Marketplace\Block\Adminhtml\Billing\Billinglist\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Action extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $orderId = $row->getData('order_id');
        $supplierId = $row->getData('supplier_id');

        $url = $this->getUrl(
            '*/*/edit',
            ['order_id' => $orderId, 'supplier_id' => $supplierId]
        );

        return sprintf("<a href='%s'>%s</a>", $url, __('Create Manual Payment'));
    }
}
