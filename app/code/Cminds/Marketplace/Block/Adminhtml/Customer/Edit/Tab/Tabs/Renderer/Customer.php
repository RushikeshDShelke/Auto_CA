<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObject ;

class Customer extends AbstractRenderer
{
    protected $customerFactory;

    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->customerFactory = $customerFactory;
    }

    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $customer = $this->customerFactory->create()->load($value);

        if ($customer->getId()) {
            $html = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            $html = $value;
        }

        return $html;
    }
}