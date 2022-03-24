<?php

namespace Cminds\Supplierfrontendproductuploader\Block;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cminds\Supplierfrontendproductuploader\Helper\Product\Media\Video;

class Product extends Template
{
    /**
     * Registry object instance.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Product factory instance.
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Product media video helper instance.
     *
     * @var Video
     */
    protected $videoHelper;

    public function __construct(
        Registry $registry,
        Context $context,
        ProductFactory $productFactory,
        Video $video
    ) {
        $this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->videoHelper = $video;

        parent::__construct($context);
    }

    /**
     * Get current loaded product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $id = $this->registry->registry('supplier_product_id');

        /*
         * Commented this line because on the edit page of the downloadable product
         * we try to get product in the method getLinks(). At the moment when we retrieve 
         * id from registry, the registry value 'supplier_product_id' is already empty.  
         */
        //$this->registry->unregister('supplier_product_id');

        $product = $this->productFactory->create()->load($id);

        return $product;
    }

    /**
     * Get product attributes.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getProductAttributes($product)
    {
        $data = [];
        $attributes = $product->getAttributes();

        foreach ($attributes as $attribute) {
            $value = $attribute->getFrontend()->getValue($product);
            $data[$attribute->getAttributeCode()] = $value;
        }

        return $data;
    }

    /**
     * Get POST data.
     *
     * @return array
     */
    public function getPost()
    {
        $post = $this->_request->getPost();

        return $post;
    }

    /**
     * Get product media video helper instance.
     *
     * @return Video
     */
    public function getProductMediaVideoHelper()
    {
        return $this->videoHelper;
    }
}
