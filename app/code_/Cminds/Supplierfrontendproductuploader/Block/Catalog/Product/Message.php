<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Catalog\Product;


use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;


class Message extends Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * Get product id
     * @return int
     */
    public function getProductId()
    {
        return $this->_product->getId();
    }

    /**
     * Get the refresh URL
     * @return string
     */
    public function getRefreshUrl()
    {
        return $this->getUrl('supplier/message/refresh');
    }

    /**
     * Check if product notification message needed
     * @return bool
     */
    public function isMessageNeeded()
    {

        $product = $this->getProduct();
        $showProduct = $product->isVisibleInCatalog()
            && $product->isVisibleInSiteVisibility();

        // this situation is valid only for logged customers
        if (
            !$showProduct
            && $product->getFrontendproductProductStatus() != 1
            && $product->getCreatorId()
        ) {
            return true;
        } else {
            return false;
        }
    }
}

