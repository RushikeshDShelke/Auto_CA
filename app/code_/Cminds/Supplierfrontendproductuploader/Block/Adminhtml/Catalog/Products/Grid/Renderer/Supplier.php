<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Catalog\Products\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject ;

class Supplier extends AbstractRenderer
{
    private $customer;

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
        $lastName = $row->getData('supplier_lastname');
        $firstName = $row->getData('supplier_firstname');

        return $firstName . ' ' . $lastName;
    }
}
