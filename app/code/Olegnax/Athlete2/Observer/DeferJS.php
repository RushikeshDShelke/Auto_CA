<?php
/**
 * @author      Olegnax
 * @package     Olegnax_DeferJS
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 *
 * @noinspection PhpUndefinedClassInspection
 */

namespace Olegnax\Athlete2\Observer;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Cache;
use Magento\Framework\App\Cache\State;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\Cache\Type;
use Olegnax\Athlete2\Helper\Helper;
use Psr\Log\LoggerInterface;

/**
 * Class for modifying js elements in the page source
 */
class DeferJS implements ObserverInterface
{
    const DEFERJS = 'general/deferjs';
    const DEFERJS_USE_CACHE = 'general/deferjs_use_cache';
    const DEFERJS_COMBINE_INLINE = 'general/deferjs_combine_inline';
    const DEFERJS_COMBINE_MAGENTOINIT = 'general/deferjs_combine_magentoinit';
    const DEFERJS_EXCLUDE_HOMEPAGE = 'general/deferjs_exclude_homepage';
    const DEFERJS_EXCLUDE_ACTION = 'general/deferjs_exclude_action';
    const DEFERJS_EXCLUDE_PATH = 'general/deferjs_exclude_path';
    const DEFERJS_SHOW_ACTION_PATH = 'general/deferjs_show_action_path';

    const CACHE_KEY_PREFIX = 'OX_DEFERJS_';
    const TYPE_IDENTIFIER = Type::TYPE_IDENTIFIER;
    const CACHE_TAG = Type::CACHE_TAG;
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Cache
     */
    protected $_cache;
    /**
     * @var State
     */
    protected $_cacheState;
    /**
     * @var bool
     */
    protected $_useCache;

    /**
     * FrontSendResponseBefore constructor.
     *
     * @param Helper $helper
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param State $cacheState
     * @param Type $cache
     * @param Json|null $serializer
     */
    public function __construct(
        Helper $helper,
        RequestInterface $request,
        LoggerInterface $logger,
        State $cacheState,
        Type $cache,
        Json $serializer = null
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->logger = $logger;
        $this->_cacheState = $cacheState;
        $this->_cache = $cache ?: $helper->_loadObject(Type::class);
        $this->serializer = $serializer ?: $helper->_loadObject(Json::class);
    }

    /**
     * @param string[] $value
     * @return string
     */
    public function mapCondition($value)
    {
        if (is_array($value) && array_key_exists('condition', $value)) {
            $value = $value['condition'];
        }
        return $value;
    }

    /**
     * @return bool
     */
    public function useCache() {
        if (!is_bool($this->_useCache)) {
            $this->_useCache = (bool) $this->helper->getModuleConfig(static::DEFERJS_USE_CACHE);
        }
        return $this->_useCache;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->allowedDefer()) {
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
                $response->setBody($html);
                return;
            }
        }

        $html = $this->defer($html);
        $html = $this->showActionPath($html);

        if ($this->useCache()) {
            $this->_saveCache($html, $response);
        }
        $response->setBody($html);
    }

    /**
     * @return bool
     */
    protected function allowedDefer()
    {
        if (!$this->helper->isEnabled()
            || !$this->getConfig(static::DEFERJS)
            || $this->request->isXmlHttpRequest()
            || (
                $this->getConfig(static::DEFERJS_EXCLUDE_HOMEPAGE)
                && $this->helper->isHomePage()
            )
        ) {
            return false;
        }

        $exclude_action = $this->getConfig(static::DEFERJS_EXCLUDE_ACTION);
        $exclude_path = $this->getConfig(static::DEFERJS_EXCLUDE_PATH);
        try {
            $exclude_action = empty($exclude_action) ? [] : $this->serializer->unserialize($exclude_action);
        } catch (InvalidArgumentException $e) {
            $exclude_action = [];
        }
        $exclude_action = array_map([$this, 'mapCondition'], $exclude_action);

        try {
            $exclude_path = empty($exclude_path) ? [] : $this->serializer->unserialize($exclude_path);
        } catch (InvalidArgumentException $e) {
            $exclude_path = [];
        }
        $exclude_path = array_map([$this, 'mapCondition'], $exclude_path);

        $action = $this->request->getFullActionName();
        $path = $this->request->getPathInfo();
        $org_path = $this->request->getOriginalPathInfo();
        if (in_array($action, $exclude_action)
            || in_array($path, $exclude_path)
            || in_array($org_path, $exclude_path)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function getConfig($path = '')
    {
        return $this->helper->getModuleConfig($path);
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
     * @param string $html
     * @return string
     */
    protected function defer($html)
    {
        $pattern = '@<script([^<>]*+(?<!text/x-magento-template.| nodefer))>(.*?)</script>@ims';
        if (preg_match_all($pattern, $html, $_matches)) {
            $html_js = implode('', $_matches[0]);

            // Combine magento init scripts
            if ($this->getConfig(static::DEFERJS_COMBINE_MAGENTOINIT)) {
                $html_js = $this->replaceMagentoInit($html_js);
            }
            // Combine inline scripts
            if ($this->getConfig(static::DEFERJS_COMBINE_INLINE)) {
                $html_js = $this->implodeScript($html_js);
            }
            $html = preg_replace($pattern, '', $html);
            if (preg_match('@</body>@i', $html)) {
                $html = preg_replace('@</body>@i', '</body>', $html);
                $html = str_replace('</body>', $html_js . '</body>', $html);
            } else {
                $html .= $html_js;
            }
        }
        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function replaceMagentoInit($html)
    {
        $pattern = '@<script[^<>]*type="text/x-magento-init"[^<>]*>(.+)</script>@msU';
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[1] as $key => $value) {
                try {
                    $_value = $this->serializer->unserialize($value);
                    $matches[1][$key] = is_array($_value) ? $_value : [];
                    $html = str_replace($matches[0][$key], '', $html);
                } catch (Exception $exception) {
                    $this->logger->error(__('Wrong Json structure: ' . $value));
                    unset($matches[1][$key]);
                }
            }
            /** @var array $matches */
            $matches = call_user_func_array('array_replace_recursive', $matches[1]);
            $js = '<script type="text/x-magento-init">' . $this->serializer->serialize($matches) . '</script>';
            $html .= $js;
        }

        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function implodeScript($html)
    {
        $pattern = '@(<script[^<>]*>)(.*)</script>@msU';
        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {

            $new_html = [];
            $script = [];
            foreach ($matches as $match) {
                if (preg_match('@src="@i', $match[1])) {
                    if (!empty($script)) {
                        $script[1] = preg_replace('@^\s+@', '', $script[1]);
                        $script[1] = preg_replace('@\s+$@', '', $script[1]);
                        $new_html[] = $script[0] . $script[1] . '</script>';
                        $script = [];
                    }
                    $new_html[] = $match[0];
                    continue;
                }
                if (preg_match('@type="(text|application)/javascript"@i', $match[1]) ||
                    !preg_match('@type="([^"]+)"@i', $match[1])
                ) {
                    if (empty($script)) {
                        $script[0] = $match[1];
                        $script[1] = '';
                    }
                    $script[1] .= rtrim(trim($match[2]), ';') . ";\n";
                } else {
                    $new_html[] = $match[0];
                }
            }
            if (!empty($script)) {
                $new_html[] = $script[0] . $script[1] . '</script>';
            }
            $html = implode('', $new_html);
        }

        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function showActionPath($html)
    {
        if ($this->getConfig(static::DEFERJS_SHOW_ACTION_PATH)) {
            $new_html = '<table>
<tr><td>' . __('Action') . '</td><td>' . $this->request->getFullActionName() . '</td></tr>
<tr><td>' . __('Path') . '</td><td>' . $this->request->getPathInfo() . '</td></tr>
<tr><td>' . __('Original Path') . '</td><td>' . $this->request->getOriginalPathInfo() . '</td></tr>
</table>';
            if (preg_match('@</body>@i', $html)) {
                $html = preg_replace('@</body>@i', $new_html . '</body>', $html);
            } else {
                $html .= $new_html;
            }
        }

        return $html;
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
