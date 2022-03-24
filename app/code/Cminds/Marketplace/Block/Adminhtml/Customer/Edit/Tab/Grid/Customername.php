<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject ;

class Customername extends AbstractRenderer
{
    protected $_customer;

    public function __construct(
        Context $context,
        Customer $customer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->_customer = $customer;
    }

    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $customer = $this->_customer->load($value);

        if ($customer->getId()) {
            $ret = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            $ret = $value;
        }

        return $ret;
    }
}
