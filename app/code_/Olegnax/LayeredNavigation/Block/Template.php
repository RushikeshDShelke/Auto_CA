<?php

/**
 * A Magento 2 module named Olegnax/LayeredNavigation
 * Copyright (C) 2019
 *
 * This file is part of Olegnax/LayeredNavigation.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Block
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2010-2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\LayeredNavigation\Block;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Olegnax\Core\Block\Template as coreTemplate;
use Olegnax\LayeredNavigation\Helper\Helper;

class Template extends coreTemplate
{
    protected $helper;
    /**
     * @var Json|mixed|null
     */
    protected $json;

    public function __construct(
        Context $context,
        Helper $helper,
        Json $json = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $data);
    }

    public function getNavConfig()
    {
        $config = [
            'clearUrl' => $this->getClearUrl(),
            'scrollToTop' => 1,
            'loaderFilter' => (bool)$this->getModuleLoadersConfig('loader_type_filters'),
            'loaderContent' => (bool)$this->getModuleLoadersConfig('loader_type_content'),
            'loaderPage' => (bool)$this->getModuleLoadersConfig('loader_type_page'),
            'abortPrev' => (bool)$this->helper->getModuleConfig('general/abort_prev_request'),
        ];
        if ($config['loaderContent']) {
            $config['loaderContentSelector'] = $this->getSelectorProducts();
        }

        return $this->json->serialize($config);
    }

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        $filterState = [];
        foreach ($this->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return $this->_urlBuilder->getUrl('*/*/*', $params);
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    public function getActiveFilters()
    {
        $filters = $this->getLayer()->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = [];
        }
        return $filters;
    }

    /**
     * Retrieve Layer object
     *
     * @return Layer
     */
    public function getLayer()
    {
        if (!$this->hasData('layer')) {
            $_catalogLayer = $this->_loadObject(Resolver::class)->get();
            $this->setLayer($_catalogLayer);
        }
        return $this->_getData('layer');
    }

    /**
     * @param string $path
     * @param null $storeCode
     * @return mixed
     */
    public function getModuleLoadersConfig($path = '', $storeCode = null)
    {
        return $this->helper->getModuleConfig('loaders/' . $path, $storeCode);
    }

    /**
     * @return string
     */
    public function getSelectorProducts()
    {
        $products_container = $this->getModuleLoadersConfig('loader_content_selector') ?: '.column.main .products.products-grid, .column.main .products.products-list, .column.main .products-grid.grid';
        return $products_container;
    }

}
