<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\App\Cache\State;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\Cache\Type;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Athlete2\Helper\LazyLoad as HelperLazyLoad;

class LazyLoad implements ObserverInterface
{
    const CACHE_KEY_PREFIX = 'OX_LazyLoad_';
    const TYPE_IDENTIFIER = Type::TYPE_IDENTIFIER;
    const CACHE_TAG = Type::CACHE_TAG;
    const LAZY_USE_CACHE = 'general/lazy_use_cache';
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var State
     */
    protected $_cacheState;
    /**
     * @var Type
     */
    protected $_cache;
    /**
     * @var Json|null
     */
    protected $serializer;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Helper
     */
    protected $lazyLoad;
    /**
     * @var bool
     */
    protected $_useCache;

    /**
     * FrontSendResponseBefore constructor.
     *
     * @param Helper $helper
     * @param HelperLazyLoad $LazyLoad
     * @param RequestInterface $request
     * @param State $cacheState
     * @param Type $cache
     * @param Json|null $serializer
     */
    public function __construct(
        Helper $helper,
        HelperLazyLoad $LazyLoad,
        RequestInterface $request,
        State $cacheState,
        Type $cache,
        Json $serializer = null
    ) {
        $this->helper = $helper;
        $this->lazyLoad = $LazyLoad;
        $this->request = $request;
        $this->_cacheState = $cacheState;
        $this->_cache = $cache ?: $helper->_loadObject(Type::class);
        $this->serializer = $serializer ?: $helper->_loadObject(Json::class);
    }

    /**
     * @return bool
     */
    public function useCache() {
        if (!is_bool($this->_useCache)) {
            $this->_useCache = (bool) $this->helper->getModuleConfig(static::LAZY_USE_CACHE);
        }
        return $this->_useCache;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->lazyLoad->isEnabled()
            ||
            (
                $this->request->isXmlHttpRequest() &&
                $this->request->isAjax()
            )
        ) {
            return;
        }

        /** @var ResponseHttp $response */
        $response = $observer->getEvent()->getData('response');
        if (!$response) {
            return;
        }
        $html = $response->getBody();
        if (empty($html)) {
            return;
        }
        if ($this->useCache()) {
            $_html = $this->_loadCache();
            if (!empty($_html)) {
                $response->setBody($_html);
                return;
            }
        }

        $html = $this->lazyLoad->replaceImageToLazy($html);

        if ($this->useCache()) {
            $this->_saveCache($html, $response);
        }
        $response->setBody($html);
    }

    /**
     * Load response from cache storage
     *
     * @return string
     */
    protected function _loadCache()
    {
        $cacheKey = $this->getCacheKey();
        $html = $this->_cache->load($cacheKey);
        return $html;
    }

    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(ResponseHttp::COOKIE_VARY_STRING),
        ];

        $key = sha1($this->serializer->serialize($data));

        return static::CACHE_KEY_PREFIX . $key;
    }

    /**
     * Save response content to cache storage
     *
     * @param string $html
     * @param ResponseHttp $response
     * @return void
     */
    protected function _saveCache($html, ResponseHttp $response)
    {
        if ($this->_cacheState->isEnabled(self::TYPE_IDENTIFIER)
            && (
                $response->getHttpResponseCode() == 200
                || $response->getHttpResponseCode() == 404
            )
            && (
                $this->request->isGet()
                || $this->request->isHead()
            )
        ) {
            $tagsHeader = $response->getHeader('X-Magento-Tags');
            $tags = $tagsHeader ? explode(',', $tagsHeader->getFieldValue()) : [];

            $cacheKey = $this->getCacheKey();
            $tags[] = self::CACHE_TAG;
            $this->_cache->save($html, $cacheKey, array_unique($tags));
        }
    }
}
