<?php

namespace Olegnax\Athlete2\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class LazyLoad extends AbstractHelper
{

    const PLACEHOLDERE = 'athlete2/placeholder.png';
    const PLACEHOLDER_TEMPLATE = '{dirname}/lazy-placeholders/{width}_{height}.{extension}';
    const SVG_ENABLED = 'athlete2_settings/general/lazyload_svg';
    const EXCLUDE = 'athlete2_settings/general/lazyload_exclude';
    const RESIZED_PLACEHOLDER = 'athlete2_settings/general/lazyload_resized_placeholder';
    const SIMPLE = 'athlete2_settings/general/lazyload_simple';
    const PRELOADER_IMG = 'Olegnax_Core/images/preloader-img.svg';
    protected $_lazyExcludeClass;
    protected $isEnabled;
    /**
     * @var Helper
     */
    protected $athleteHelper;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var string
     */
    protected $urlMedia;
    /**
     * @var Image
     */
    protected $imageHelper;
    /**
     * @var false|string
     */
    protected $placeholder;
    /**
     * @var string
     */
    protected $placeholderDefault;
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * LazyLoad constructor.
     * @param Context $context
     * @param Image $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param Helper $helper
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        Helper $helper
    ) {
        $this->athleteHelper = $helper;
        $this->escaper = $escaper;
        $this->imageHelper = $imageHelper;
        $this->urlMedia = rtrim(preg_replace(
            '@^http[s]*\:@i',
            '',
            $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
        ), "/");

        parent::__construct($context);
    }

    /**
     * @param string $html
     * @return bool
     */
    public function _filterImg($html = '')
    {
        if (preg_match('@(data-original=\"|lazy|data-ox-image)@i', $html)) {
            return false;
        }
        $class = $this->getExcludeClass();
        if (!empty($class) && preg_match('@class="([^"]+)"@i', $html, $matches)) {
            $matches = array_filter(explode(' ', $matches[1]));
            $intersect = array_intersect($class, $matches);
            return empty($intersect);
        }
        return true;
    }

    /**
     * @return array|mixed|string[]
     */
    public function getExcludeClass()
    {
        if (empty($this->_lazyExcludeClass)) {
            $class = $this->getConfig(static::EXCLUDE);
            if (empty($class)) {
                $class = ['rev-slidebg'];
            } elseif (preg_match_all('@\S+@', $class, $matches)) {
                $class = array_filter($matches[0]);
            }
            if (is_array($class) && !empty($class)) {
                $this->_lazyExcludeClass = $class;
            }
        }

        return $this->_lazyExcludeClass;
    }

    /**
     * @param string $path
     * @param string $storeCode
     * @return mixed
     */
    public function getConfig($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * @param string $html
     * @return string|string[]
     */
    public function replaceImageToLazy($html = '')
    {
        if ($this->isEnabled() && preg_match_all('@<img[^<>]+src\="[^<>]+>@ims', $html, $matches)) {

            $matches = array_filter($matches[0], [$this, '_filterImg']);
            if (!empty($matches) && is_array($matches)) {
                $_matches = [];
                $resizedPlaceholder = '1' == $this->getConfig(static::RESIZED_PLACEHOLDER);
                $simple = '1' == $this->getConfig(static::SIMPLE);
                foreach ($matches as $htmlImg) {
                    $_matches[$htmlImg] = $this->modifyImg($htmlImg, $simple, $resizedPlaceholder);
                }
                if (!empty($_matches)) {
                    $search = array_keys($_matches);
                    $replace = array_values($_matches);
                    $html = str_replace($search, $replace, $html);
                }
            }
        }

        return $html;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (is_null($this->isEnabled)) {
            $this->isEnabled = $this->athleteHelper->isLazyLoadEnabled();
        }

        return $this->isEnabled;
    }

    /**
     * @param string $html
     * @param bool $simple
     * @param bool $resizedPlaceholder
     * @return string
     */
    protected function modifyImg($html, $simple = false, $resizedPlaceholder = false)
    {
        $encoded = false !== strpos($html, '\"');
        if ($encoded) {
            $html = json_decode('"' . $html . '"');
        }

        if (!$simple) {
            $placeholder = $this->getDefaultPlaceHolder();
            if ($resizedPlaceholder) {
                $size = [];
                if (preg_match('@width="(\d+)"@im', $html, $matches)) {
                    $size[] = (int)$matches[1];
                }
                if (preg_match('@height="(\d+)"@im', $html, $matches)) {
                    $size[] = (int)$matches[1];
                }
                $size = array_filter($size);
                if (2 != count($size) && preg_match('@src="([^"]+)"@im', $html, $matches)) {
                    $size = $this->getSizeImageFromUrl($matches[1]);
                }
                if (2 == count($size)) {
                    if ($this->isSvg()) {
                        $placeholder = $this->createPlaceholder($size);
                    } else {
                        try {
                            $image = $this->getPlaceholderHelper()->adaptiveResize($size)->getUrl();
                            $placeholder = $image;
                        } catch (Exception $e) {
                            $this->_logger->error($e->getMessage());
                        }
                    }
                    if (!preg_match('@ height="@i', $html)) {
                        $html = preg_replace('@(src="[^\"]+")@im', '$1 height="' . $size[1] . '"', $html);
                    }
                    if (!preg_match('@ width="@i', $html)) {
                        $html = preg_replace('@(src="[^\"]+")@im', '$1 width="' . $size[0] . '"', $html);
                    }
                }
            }
            $html = preg_replace('@src="([^\"]+)"@im', 'src="' . $placeholder . '" data-original="$1"', $html);

            $html = preg_replace('@class=""@i', '', $html);
            if (preg_match('@class="@i', $html)) {
                $html = preg_replace('@class="([^\"]+)"@im', 'class="$1 lazy"', $html);
            } else {
                $html = preg_replace('@<img@im', '$0 class="lazy"', $html);
            }
        }

        if (!preg_match('@loading="lazy"@i', $html) && !preg_match('@src="data@i', $html)) {
            $html = preg_replace('@<img@im', '$0 loading="lazy"', $html);
        }

        $html = preg_replace('@ {2,}@', ' ', preg_replace('@[\r\n\t]+@', ' ', $html));
        if ($encoded) {
            $html = trim(json_encode($html), '"');
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getDefaultPlaceHolder()
    {
        if ($this->isSvg()) {
            $this->placeholderDefault = $this->createPlaceholder();
        }
        if (empty($this->placeholderDefault)) {
            $this->placeholderDefault = $this->getViewFileUrl(static::PRELOADER_IMG);
        }
        return $this->placeholderDefault;
    }

    /**
     * @return bool
     */
    public function isSvg()
    {
        return (bool)$this->athleteHelper->getSystemValue(static::SVG_ENABLED);
    }

    /**
     * @param int[] $size
     * @return string
     */
    public function createPlaceholder($size = [1, 1])
    {
        $html = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $size[0] . ' ' .
            $size[1] . '" width="' . $size[0] . '" height="' . $size[1] . '"></svg>';
        return $this->escaper->escapeHtmlAttr($html);
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_loadObject(RequestInterface::class)->isSecure()], $params);
            return $this->_loadObject(Repository::class)->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            return $this->_getNotFoundUrl();
        }
    }

    /**
     * @param string $object
     * @return mixed
     */
    protected function _loadObject($object)
    {
        return ObjectManager::getInstance()->get($object);
    }

    /**
     * Get 404 file not found url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
    {
        return $this->getUrl($route, $params);
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_loadObject(UrlInterface::class)->getUrl($route, $params);
    }

    /**
     * @param string $path
     * @return array|int[]
     */
    protected function getSizeImageFromUrl($path)
    {
        if (!empty($path)) {
            return $this->getSizeImage($this->getAbsoluteImagePath($path));
        }
        return [];
    }

    /**
     * @param $path
     * @return array
     */
    protected function getSizeImage($path)
    {
        try {
            $image = $this->imageHelper->init($path);
            if (!$image->getOriginalWidth() || !$image->getOriginalHeight()) {
                throw new Exception(__('Image not found: ' . $path));
            }

            return [$image->getOriginalWidth(), $image->getOriginalHeight()];
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return [];
    }

    /**
     * @param string $url
     * @return string|string[]|null
     */
    protected function getAbsoluteImagePath($url)
    {
        $url = preg_replace(
            '@^http[s]*\:@i',
            '',
            $url
        );
        if (false === strpos($url, $this->urlMedia)) {
            $path = preg_replace('@^\/media@i', '', $url);
        } else {
            $path = str_replace($this->urlMedia, '', $url);
        }

        return $path;
    }

    /**
     * @return Image
     * @throws Exception
     */
    protected function getPlaceholderHelper()
    {
        return $this->imageHelper->init(
            static::PLACEHOLDERE,
            [
                'fileTemplate' => static::PLACEHOLDER_TEMPLATE,
                'quality' => 1,
            ]
        );
    }

    /**
     * @param string $url
     * @return string
     */
    public function getPlaceHolder($url)
    {
        $size = $this->getSizeImageFromUrl($url);
        if (!empty($size)) {
            if ($this->isSvg()) {
                return $this->createPlaceholder($size);
            } else {
                try {
                    $image = $this->getPlaceholderHelper()->adaptiveResize($size)->getUrl();
                    return $image;
                } catch (Exception $e) {
                    $this->_logger->error($e->getMessage());
                }
            }
        }
        return $this->getDefaultPlaceHolder();
    }
}