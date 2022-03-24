<?php

namespace Cminds\Marketplace\Block\Adminhtml\Billing\Billinglist\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject ;

class Supplier extends AbstractRenderer
{
    protected $customer;

    public function __construct(
        Context $context,
        Customer $customer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->customer = $customer;
    }

    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $customer = $this->customer->load($value);

        if ($customer->getId()) {
            $ret = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            $ret = $value;
        }

        return $ret;
    }
}