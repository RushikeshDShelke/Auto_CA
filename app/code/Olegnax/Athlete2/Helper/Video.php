<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

class Video extends AbstractHelper
{
    const ORDER_MIME = [
        'video/webm',
		'video/mp4',
        'video/ogg',
    ];

    const TEMPLATE_VIDEO = 'Olegnax_Athlete2::video.phtml';
    /**
     * @var ReadInterface
     */
    protected $_mediaDirectory;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var LayoutInterface
     */
    protected $_layout;
    /**
     * @var array
     */
    protected $_productVideos;

    /**
     * Video constructor.
     * @param Context $context
     * @param LayoutInterface $layout
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        LayoutInterface $layout,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->_layout = $layout;
        $this->_mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->_productVideos = [];
        parent::__construct($context);
    }

    /**
     * @param array $attributes
     * @return string
     */
    public static function prepareAttributes(array $attributes)
    {
        $escaper = ObjectManager::getInstance()->get(Escaper::class);
        $attributes = array_filter($attributes);
        if (empty($attributes)) {
            return '';
        }
        $html = '';
        foreach ($attributes as $attributeName => $attributeValue) {
            if (is_bool($attributeValue)) {
                if ($attributeValue) {
                    $html .= sprintf(
                        ' %s',
                        $attributeName
                    );
                }
            } else {
                $html .= sprintf(
                    ' %s="%s"',
                    $attributeName,
                    $escaper->escapeHtmlAttr($attributeValue)
                );
            }
        }

        return $html;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function issetVideo($product)
    {
        $files = $this->_getVideo($product);

        return !empty($files);
    }

    /**
     * @param Product $product
     * @return string[]
     */
    private function _getVideo($product)
    {
        if (!$product) {
            return [];
        }
        $productId = $product->getId();
        if (!array_key_exists($productId, $this->_productVideos)) {
            $this->_productVideos[$productId] = [];
        }
        if (empty($this->_productVideos[$productId])) {
            $videoPath = $this->getProductData($product, 'ox_gallery_video');
            $videoPath = ltrim($videoPath, '\\\/');
            if (!empty($videoPath)) {
                $videoAbsolutePath = $this->getAbsolutePath($videoPath);
                $videoReplaceAbsolutePath = preg_replace('#\.[a-z0-9]{3,}$#i', '', $videoAbsolutePath);
                $this->_productVideos[$productId] = Glob::glob($videoReplaceAbsolutePath . '\.*');
                if (empty($this->_productVideos[$productId])) {
                    $this->_productVideos[$productId] = [];
                    if (file_exists($videoAbsolutePath)) {
                        $this->_productVideos[$productId][] = $videoAbsolutePath;
                    }
                }
            }
        }

        return $this->_productVideos[$productId];
    }

    /**
     * @param Product $product
     * @param string $key
     * @return mixed|null
     */
    private function getProductData($product, $key)
    {
        $productId = $product->getId();
        $data = $product->getData($key);
        if (null === $data) {
            $product = $product->load($productId);
            if ($product->getId() == $productId) {
                $data = $product->getData($key);
            }
        }

        return $data;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath(
        $path = ''
    ) {
        return $this->_mediaDirectory->getAbsolutePath($path);
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function stopOnClick($product)
    {
        return (bool)$this->getProductData($product, 'ox_gallery_video_stop_on_click');
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function loopVideo($product)
    {
        return (bool)$this->getProductData($product, 'ox_gallery_video_loop');
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function videoPosition($product)
    {
        return (int)$this->getProductData($product, 'ox_gallery_video_index') ?: 2;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function videoOnHover($product)
    {
        return (bool)$this->getProductData($product, 'ox_gallery_video_listing_hover');
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function autoplayVideo($product)
    {
        return (bool)$this->getProductData($product, 'ox_gallery_video_autoplay');
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function showControls($product)
    {
        return (bool)$this->getProductData($product, 'ox_gallery_video_controls');
    }

    /**
     * @param Product $product
     * @param array $config
     * @return string
     * @throws NoSuchEntityException
     */
    public function getVideo($product, $config = [])
    {
        $files = $this->_getVideo($product);

        if (!empty($files)) {
            $mimeFiles = [];
            foreach ($files as $file) {
                $fileMime = $this->_detectMimeType($file);
                $mimeFiles[$fileMime] = $this->getUrlPath($file);
            }
            if (!empty($mimeFiles)) {
                uksort($mimeFiles, [$this, 'sortByMime']);

                return $this->getLayout()
                    ->createBlock(
                        Template::class,
                        '',
                        [
                            'data' => array_replace(
                                [
                                    'mime_files' => $mimeFiles,
                                    'product' => $product,
                                ],
                                $config
                            ),
                        ]
                    )
                    ->setTemplate(static::TEMPLATE_VIDEO)
                    ->toHtml();
            }
        }

        return '';
    }

    /**
     * Internal method to detect the mime type of a file
     *
     * @param string $file File
     * @return string Mimetype of given file
     */
    protected function _detectMimeType($file)
    {
        $result = '';
        if (class_exists('finfo', false)) {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            $mime = @finfo_open($const);

            if (!empty($mime)) {
                $result = finfo_file($mime, $file);
            }

            unset($mime);
        }

        if (empty($result) && (function_exists('mime_content_type')
                && ini_get('mime_magic.magicfile'))) {
            $result = mime_content_type($file);
        }

        if (empty($result)) {
            $result = 'application/octet-stream';
        }

        return $result;
    }

    /**
     * @param string $path
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getUrlPath(
        $path = ''
    ) {
        $path = str_replace($this->getAbsolutePath(), '', $path);
        $path = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;

        return $path;
    }

    /**
     * @return LayoutInterface
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * @param string $itemPrev
     * @param string $itemNext
     * @return int
     */
    public function sortByMime(
        $itemPrev,
        $itemNext
    ) {
        $indexPrev = in_array($itemPrev, static::ORDER_MIME) ? array_search($itemPrev, static::ORDER_MIME) : 9999;
        $indexNext = in_array($itemNext, static::ORDER_MIME) ? array_search($itemNext, static::ORDER_MIME) : 9999;
        if ($indexPrev == $indexNext) {
            return 0;
        } elseif ($indexPrev > $indexNext) {
            return 1;
        }
        return -1;
    }
}