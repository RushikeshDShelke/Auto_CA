<?php

namespace Cminds\Marketplace\Block\Settings;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\Methods as MethodsModel;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Methods extends Template
{
    protected $_methods;
    protected $_marketplaceHelper;
    protected $priceHelper;

    public function __construct(
        Context $context,
        MethodsModel $methodsModel,
        MarketplaceHelper $marketplaceHelper,
        Price $priceHelper
    ) {
        parent::__construct($context);

        $this->_methods = $methodsModel;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->priceHelper = $priceHelper;
    }

    public function getSavedMethods()
    {
        $collection = $this->_methods->getCollection();
        $collection
            ->addFilter(
                'supplier_id',
                $this->_marketplaceHelper->getSupplierId()
            )
            ->load();

        return $collection;
    }

    public function getCurrentCurrencyPrice($price)
    {
        return $this->priceHelper->convertToCurrentCurrencyPrice($price);
    }
}
