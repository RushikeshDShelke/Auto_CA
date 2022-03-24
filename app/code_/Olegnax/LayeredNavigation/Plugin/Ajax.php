<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\PageCache\Model\Cache\Type;
use Olegnax\LayeredNavigation\Helper\Helper;

class Ajax
{
    const AJAX_ATTR = 'navAjax';

    const BLOCK_PRODUCTS = 'category.products.list';
    const BLOCK_PRODUCTS_SEARCH = 'search_result_list';
    const BLOCK_TITLE = 'page.main.title';
    const BLOCK_NAVIGATION = 'catalog.leftnav';
    const BLOCK_NAVIGATION_SEARCH = 'catalogsearch.leftnav';
    const BLOCK_BREADCRUMBS = 'breadcrumbs';
    const REGEX_URLS = '@aHR0c(Dov|HM6)[A-Za-z0-9_-]+@u';

    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var RawFactory
     */
    protected $resultRaw;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;
    /**
     * @var UrlHelper
     */
    private $urlHelper;
    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        Helper $helper,
        RawFactory $resultRaw,
        Http $request,
        UrlHelper $urlHelper,
        DecoderInterface $urlDecoder,
        Registry $registry,
        Json $json
    ) {
        $this->helper = $helper;
        $this->resultRaw = $resultRaw;
        $this->request = $request;
        $this->urlHelper = $urlHelper;
        $this->urlDecoder = $urlDecoder;
        $this->registry = $registry;
        $this->json = $json;
    }

    /**
     * @param string $html
     * @return string
     */
    public function removeArgs($html)
    {
        $html = str_replace(['?' . static::AJAX_ATTR . '=1&amp;', '?' . static::AJAX_ATTR . '=1&'], '?', $html);
        $html = str_replace([
            '?' . static::AJAX_ATTR . '=1',
            '&amp;' . static::AJAX_ATTR . '=1',
            '&' . static::AJAX_ATTR . '=1'
        ], '', $html);

        return $html;
    }

    public function removeEncodedArgsItem($match)
    {
        $code_string = $match[0];
        $url = $this->urlDecoder->decode($code_string);
        if (false !== $url) {
            $newUrl = $this->urlHelper->removeRequestParam($url, static::AJAX_ATTR);
            $newUrl = $this->removeEncodedArgs($newUrl);

            if ($url != $newUrl) {
                $code_string = $this->urlHelper->getEncodedUrl($newUrl);
            }
        }
        return $code_string;
    }

    /**
     * @param string $html
     * @return string
     */
    public function removeEncodedArgs($html)
    {
        $_html = preg_replace_callback(static::REGEX_URLS, [$this, 'removeEncodedArgsItem'], $html);
        if ($_html) {
            $html = $_html;
        }

        return $html;
    }

    /**
     * @return bool
     */
    protected function isAjax()
    {
        return $this->helper->isEnabled() &&
            $this->request->isXmlHttpRequest() &&
            $this->request->isAjax() &&
            $this->request->getParam(static::AJAX_ATTR, '');
    }

    protected function getAjaxContent(Page $page)
    {
        /** @var LayoutInterface $layout */
        $layout = $page->getLayout();
        $layout->getOutput(); // @todo temporary fix: magenta ignoring the product limit parameter per page, per page with ajax request
        $title = $page->getConfig()->getTitle()->get();

        $blocks = $this->getLayoutBlock($layout);

        $productsCount = 0;
        $currentCategory = 0;
        if ($blocks['products']) {
            $productsCount = $this->getProductCount($blocks['products']);
            if ($blocks['products']->getLayer()) {
                $currentCategory = $blocks['products']->getLayer()->getCurrentCategory();
                $currentCategory = $currentCategory ? $currentCategory->getId() : 0;
            }
        }

        $html = [];
        $cache_tags = [];
        foreach ($blocks as $key => $block) {
            $cache_tags = $this->addIdentities($block, $cache_tags);

            $html[$key] = $block ? $block->toHtml() : '';
        }

        $response = compact('productsCount', 'currentCategory', 'title', 'html');
        $response['html']['_before'] = '';
        $response['html']['_after'] = '';
        try {
            $response = $this->prepareForPlugins($response, $blocks);
        } catch (\Exception $exception){
            // @todo do nothing or log
        }
        $response = $this->changeResponse($response, $cache_tags, $page);
        return $response;
    }

    /**
     * @param LayoutInterface $layout
     * @return array
     */
    protected function getLayoutBlock($layout)
    {
        return [
            'products' => $layout->getBlock(self::BLOCK_PRODUCTS) ?: $layout->getBlock(self::BLOCK_PRODUCTS_SEARCH),
            'leftnav' => $layout->getBlock(self::BLOCK_NAVIGATION) ?: $layout->getBlock(self::BLOCK_NAVIGATION_SEARCH),
            'title' => $layout->getBlock(self::BLOCK_TITLE),
            'breadcrumbs' => $layout->getBlock(self::BLOCK_BREADCRUMBS),
        ];
    }

    /**
     * @param ListProduct $block
     * @return int
     */
    protected function getProductCount($block)
    {
        return (int)$block->getLoadedProductCollection()->getSize();
    }

    protected function addIdentities($block, array $data = [])
    {
        if ($block instanceof IdentityInterface) {
            $data = array_merge($data, $block->getIdentities());
        }

        return $data;
    }

    protected function changeResponse($response, $cache_tags)
    {
        $cache_tags[] = Type::CACHE_TAG;
        $cache_tags = array_unique($cache_tags);
        $cache_tags = implode(',', $cache_tags);

        $response['cache_tags'] = $cache_tags;
        $response['html'] = array_map([$this, 'removeArgs'], $response['html']);
        $response['html'] = array_map([$this, 'removeEncodedArgs'], $response['html']);

        return $response;
    }

    protected function json(array $data)
    {
        $response = $this->resultRaw->create();
        $response->setHeader('Content-type', 'application/json');
        if (array_key_exists('cache_tags', $data)) {
            if (!empty($data['cache_tags'])) {
                $response->setHeader('X-Magento-Tags', $data['cache_tags']);
            }
            unset($data['cache_tags']);
        }
        $data = $this->json->serialize($data);
        $response->setContents($data);
        return $response;
    }

    /**
     * @param array $response
     * @param array $blocks
     * @return array
     * @throws LocalizedException
     */
    protected function prepareForPlugins(array $response, array $blocks)
    {
        if (array_key_exists('products', $blocks) && class_exists('\Amasty\Label\Plugin\Catalog\Product\ListProduct')) {
            /** @var \Amasty\Label\Plugin\Catalog\Product\ListProduct $amastyLabelListProduct */
            $amastyLabelListProduct = ObjectManager::getInstance()->get('\Amasty\Label\Plugin\Catalog\Product\ListProduct');
            $this->registry->register('amlabel_category_observer', false);
            $blocks['products']->setIsAmLabelObserved(false);
            $response['html']['_after'] .= $amastyLabelListProduct->afterToHtml($blocks['products'], '');
        }

        return $response;
    }
}
