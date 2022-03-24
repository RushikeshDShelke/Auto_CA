<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Catalog\Product;

use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Supplier extends Template
{
    private $_supplierId;

    protected $_registry;
    protected $_cmindsHelper;
    protected $_objectManager;

    public function __construct(
        Context $context,
        Registry $registry,
        CmindsHelper $cmindsHelper,
        ObjectManagerInterface $objectManagerInterface
    ) {
        parent::__construct($context);

        $this->_registry = $registry;
        $this->_cmindsHelper = $cmindsHelper;
        $this->_objectManager = $objectManagerInterface;
    }


    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    public function getSupplierId()
    {
        if (!$this->_supplierId && $this->getProduct()) {
            $this->_supplierId = $this->_cmindsHelper
                ->getProductSupplierId($this->getProduct());
        }

        return $this->_supplierId;
    }

    public function isCreatedBySupplier()
    {
        return $this->getSupplierId();
    }

    public function canShowSoldBy()
    {
        $scopeConfig = $this->_objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');

        return $scopeConfig->getValue(
            'configuration'
            . '/presentation/add_sold_by_option_on_product_page'
        );
    }
}
