<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Catalog\Product\Supplier;

use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Cminds\Supplierfrontendproductuploader\Block\Catalog\Product\Supplier;

class Name extends Supplier
{
    protected $customerFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        CmindsHelper $cmindsHelper,
        ObjectManagerInterface $objectManagerInterface,
        CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $cmindsHelper,
            $objectManagerInterface
        );

        $this->customerFactory = $customerFactory;
    }

    public function _construct()
    {
        $this->setTemplate('supplier/catalog/product/supplier/name.phtml');
    }

    public function getProductSupplierName()
    {
        $supplierId = $this->getSupplierId();
        if (!$supplierId) {
            return false;
        }

        $customer = $this->customerFactory->create()->load($supplierId);
        if (!$customer->getId()) {
            return false;
        }

        if ($customer->getSupplierName()) {
            return $customer->getSupplierName();
        }

        return sprintf(
            '%s %s',
            $customer->getFirstname(),
            $customer->getLastname()
        );
    }

    public function getSupplierHelper()
    {
        return $this->_cmindsHelper;
    }
}
