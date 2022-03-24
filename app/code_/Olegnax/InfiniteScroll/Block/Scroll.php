<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Olegnax\InfiniteScroll\Helper\Helper;

class Scroll extends Template
{
    /**
     * @var Helper
     */
    public $helper;
    /**
     * @var Json
     */
    public $json;

    /**
     * Constructor
     *
     * InfiniteScroll constructor.
     * @param Context $context
     * @param Helper $helper
     * @param array $data
     * @param Json|null $json
     */
    public function __construct(
        Context $context,
        Helper $helper,
        array $data = [],
        Json $json = null
    ) {
        $this->helper = $helper;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return bool|false|string
     */
    public function getConfig()
    {

        $config = [
            'mode' => $this->getModuleConfig('type'),
            'container' => trim($this->getSelectorProducts()),
            'item' => trim($this->getSelectorProduct()),
            'loader' => (bool)$this->getModuleConfig('loader_type'),
        ];

        return $this->json->serialize($config);
    }

    /**
     * @param string $path
     * @param null $storeCode
     * @return mixed
     */
    public function getModuleConfig($path = '', $storeCode = null)
    {
        return $this->helper->getModuleConfig('general/' . $path, $storeCode);
    }

    /**
     * @return string
     */
    public function getSelectorProducts()
    {
		$products_container =  $this->getModuleConfig('products_container') ?: '.column.main .products.products-grid, .column.main .products.products-list, .column.main .products-grid.grid';
        return $products_container;
    }

    /**
     * @return string
     */
    public function getSelectorProduct()
    {
		$product_container =  $this->getModuleConfig('product_container') ?: '.item.product.product-item';
        return $product_container;
    }
}
