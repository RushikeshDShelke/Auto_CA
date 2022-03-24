<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Plugin;

use Amasty\Label\Plugin\Catalog\Product\ListProduct;
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
use Magento\PageCache\Model\Cache\Type;
use Olegnax\InfiniteScroll\Helper\Helper;

/**
 * Description of Ajax
 *
 * @author Master
 */
class Ajax
{
    const AJAX_ATTR = 'scrollAjax';

    const BLOCK_PRODUCTS = 'category.products.list';
    const BLOCK_PRODUCTS_SEARCH = 'search_result_list';
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
    protected $urlHelper;
    /**
     * @var LayoutInterface
     */
    protected $layout;
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Ajax constructor.
     * @param Helper $helper
     * @param RawFactory $resultRaw
     * @param Http $request
     * @param UrlHelper $urlHelper
     * @param DecoderInterface $urlDecoder
     * @param LayoutInterface $layout
     * @param Registry $registry
     * @param Json $json
     */
    public function __construct(
        Helper $helper,
        RawFactory $resultRaw,
        Http $request,
        UrlHelper $urlHelper,
        DecoderInterface $urlDecoder,
        LayoutInterface $layout,
        Registry $registry,
        Json $json
    ) {
        $this->helper = $helper;
        $this->resultRaw = $resultRaw;
        $this->request = $request;
        $this->urlHelper = $urlHelper;
        $this->urlDecoder = $urlDecoder;
        $this->layout = $layout;
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
            '&' . static::AJAX_ATTR . '=1',
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

    protected function getAjaxContent()
    {
        /** @var LayoutInterface $layout */
        $layout = $this->layout;
        $layout->getOutput(); // @todo temporary fix: magenta ignoring the product limit parameter per page, per page with ajax request

        $blocks = $this->getLayoutBlock($layout);

        $page = (int)$this->request->getParam('p', 1);
        $firstNum = 0;
        $lastNum = 0;
        $productsCount = 0;
        $isFirst = false;
        $isLast = false;
        if ($blocks['products']) {
            $collection = $blocks['products']->getLoadedProductCollection();

            $pageSize = $collection->getPageSize() * 1;
            $firstNum = $pageSize * ($page - 1) + 1;
            $lastNum = $pageSize * ($page - 1) + $collection->count();
            $productsCount = $collection->getSize();
            $isFirst = $firstNum <= 1;
            $isLast = $lastNum >= $productsCount;
        }

        $html = [];
        $cache_tags = [];
        foreach ($blocks as $key => $block) {
            $cache_tags = $this->addIdentities($block, $cache_tags);

            $html[$key] = $block ? $block->toHtml() : '';
        }

        $response = compact('productsCount', 'page', 'pageSize', 'firstNum', 'lastNum', 'isFirst', 'isLast', 'html');
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
        ];
    }

    protected function addIdentities($block, array $data = [])
    {
        if ($block instanceof IdentityInterface) {
            $data = array_merge($data, $block->getIdentities());
        }

        return $data;
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
            /** @var ListProduct $amastyLabelListProduct */
            $amastyLabelListProduct = ObjectManager::getInstance()->get('\Amasty\Label\Plugin\Catalog\Product\ListProduct');
            $this->registry->register('amlabel_category_observer', false);
            $blocks['products']->setIsAmLabelObserved(false);
            $response['html']['_after'] .= $amastyLabelListProduct->afterToHtml($blocks['products'], '');
        }

        return $response;
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
}
