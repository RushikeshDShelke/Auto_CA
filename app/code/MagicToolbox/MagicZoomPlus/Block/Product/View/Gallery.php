<?php

/**
 * Magic Zoom Plus view block
 *
 */
namespace MagicToolbox\MagicZoomPlus\Block\Product\View;

use Magento\Framework\Data\Collection;
use MagicToolbox\MagicZoomPlus\Helper\Data;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    public $magicToolboxHelper = null;

    /**
     * MagicZoomPlus module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass
     */
    public $toolObj = null;

    /**
     * Rendered gallery HTML
     *
     * @var array
     */
    protected $renderedGalleryHtml = [];

    /**
     * ID of the current product
     *
     * @var integer
     */
    protected $currentProductId = null;

    /**
     * Do reload product
     *
     * @var bool
     */
    protected $doReloadProduct = false;

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->magicToolboxHelper = $objectManager->get(\MagicToolbox\MagicZoomPlus\Helper\Data::class);
        $this->toolObj = $this->magicToolboxHelper->getToolObj();

        $version = $this->magicToolboxHelper->getMagentoVersion();
        if (version_compare($version, '2.2.5', '<')) {
            $this->doReloadProduct = true;
        }

        //NOTE: for versions 2.2.x (x >=9), 2.3.x (x >=2)
        if (class_exists('\Magento\Catalog\Block\Product\View\GalleryOptions')) {
            $galleryOptions = $objectManager->get(\Magento\Catalog\Block\Product\View\GalleryOptions::class);
            $this->setData('gallery_options', $galleryOptions);
        }

        //NOTE: for versions 2.3.x (x >=2)
        if (version_compare($version, '2.3.2', '>=')) {
            $imageHelper = $objectManager->get(\Magento\Catalog\Helper\Image::class);
            $this->setData('imageHelper', $imageHelper);
        }
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Magento\Framework\Data\Collection
     */
    public function getGalleryImagesCollection($product = null)
    {
        static $images = [];
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $id = $product->getId();
        if (!isset($images[$id])) {
            if ($this->doReloadProduct) {
                $productRepository = \Magento\Framework\App\ObjectManager::getInstance()->get(
                    \Magento\Catalog\Model\ProductRepository::class
                );
                $product = $productRepository->getById($product->getId());
            }

            $images[$id] = $product->getMediaGalleryImages();
            if ($images[$id] instanceof \Magento\Framework\Data\Collection) {
                $baseMediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $baseStaticUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
                foreach ($images[$id] as $image) {
                    /* @var \Magento\Framework\DataObject $image */

                    $mediaType = $image->getMediaType();
                    if ($mediaType != 'image' && $mediaType != 'external-video') {
                        continue;
                    }

                    $img = $this->_imageHelper->init($product, 'product_page_image_large', ['width' => null, 'height' => null])
                            ->setImageFile($image->getFile())
                            ->getUrl();

                    $iPath = $image->getPath();
                    if (!is_file($iPath)) {
                        if (strpos($img, $baseMediaUrl) === 0) {
                            $iPath = str_replace($baseMediaUrl, '', $img);
                            $iPath = $this->magicToolboxHelper->getMediaDirectory()->getAbsolutePath($iPath);
                        } else {
                            $iPath = str_replace($baseStaticUrl, '', $img);
                            $iPath = $this->magicToolboxHelper->getStaticDirectory()->getAbsolutePath($iPath);
                        }
                    }
                    try {
                        $originalSizeArray = getimagesize($iPath);
                    } catch (\Exception $exception) {
                        $originalSizeArray = [0, 0];
                    }

                    if ($mediaType == 'image') {
                        if ($this->toolObj->params->checkValue('square-images', 'Yes')) {
                            $bigImageSize = ($originalSizeArray[0] > $originalSizeArray[1]) ? $originalSizeArray[0] : $originalSizeArray[1];
                            $img = $this->_imageHelper->init($product, 'product_page_image_large')
                                    ->setImageFile($image->getFile())
                                    ->resize($bigImageSize)
                                    ->getUrl();
                        }
                        $image->setData('large_image_url', $img);

                        list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb', $originalSizeArray);
                        $medium = $this->_imageHelper->init($product, 'product_page_image_medium', ['width' => $w, 'height' => $h])
                                ->setImageFile($image->getFile())
                                ->getUrl();
                        $image->setData('medium_image_url', $medium);
                    }

                    list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('selector', $originalSizeArray);
                    $thumb = $this->_imageHelper->init($product, 'product_page_image_small', ['width' => $w, 'height' => $h])
                            ->setImageFile($image->getFile())
                            ->getUrl();
                    $image->setData('small_image_url', $thumb);
                }
            }
        }
        return $images[$id];
    }

    /**
     * Retrieve original gallery block
     *
     * @return mixed
     */
    public function getOriginalBlock()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        return is_null($data) ? null : $data['blocks']['product.info.media.image'];
    }

    /**
     * Retrieve another gallery block
     *
     * @return mixed
     */
    public function getAnotherBlock()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        if ($data) {
            $skip = true;
            foreach ($data['blocks'] as $name => $block) {
                if ($name == 'product.info.media.magiczoomplus') {
                    $skip = false;
                    continue;
                }
                if ($skip) {
                    continue;
                }
                if ($block) {
                    return $block;
                }
            }
        }
        return null;
    }

    /**
     * Check for installed modules, which can operate in cooperative mode
     *
     * @return bool
     */
    public function isCooperativeModeAllowed()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        return is_null($data) ? false : $data['cooperative-mode'];
    }

    /**
     * Get thumb switcher initialization attribute
     *
     * @param integer $id
     * @return string
     */
    public function getThumbSwitcherInitAttribute($id = null)
    {
        static $html = null;
        if ($html === null) {
            if (is_null($id)) {
                $id = $this->currentProductId;
            }
            $settings = $this->magicToolboxHelper->getVideoSettings();
            $settings['tool'] = 'magiczoomplus';
            $settings['switchMethod'] = $this->toolObj->params->getValue('selectorTrigger');
            if ($settings['switchMethod'] == 'hover') {
                $settings['switchMethod'] = 'mouseover';
            }
            $settings['productId'] = $id;
            $html = ' data-mage-init=\'{"magicToolboxThumbSwitcher": '.json_encode($settings).'}\'';
        }
        return $html;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->renderGalleryHtml();
        return parent::_beforeToHtml();
    }

    /**
     * Get rendered HTML
     *
     * @param integer $id
     * @return string
     */
    public function getRenderedHtml($id = null)
    {
        if (is_null($id)) {
            $id = $this->getProduct()->getId();
        }
        return isset($this->renderedGalleryHtml[$id]) ? $this->renderedGalleryHtml[$id] : '';
    }

    /**
     * Render gallery block HTML
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAssociatedProduct
     * @param array $data
     * @return $this
     */
    public function renderGalleryHtml($product = null, $isAssociatedProduct = false, $data = [])
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $this->currentProductId = $id = $product->getId();
        if (!isset($this->renderedGalleryHtml[$id])) {
            $this->toolObj->params->setProfile('product');
            $name = $product->getName();
            $productImage = $product->getImage();
            $mainHTML = '';
            $defaultContainerId = 'mtImageContainer';
            $containersData = [
                'mtImageContainer' => '',
                'mt360Container' => '',
                'mtVideoContainer' => '',
            ];
            $selectorsArray = [];

            $images = $this->getGalleryImagesCollection($product);

            $originalBlock = $this->getOriginalBlock();

            if (!$images->count()) {
                $this->renderedGalleryHtml[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                return $this;
            }

            $selectorIndex = 0;
            $baseIndex = 0;
            foreach ($images as $image) {

                $mediaType = $image->getMediaType();
                $isImage = $mediaType == 'image';
                $isVideo = $mediaType == 'external-video';

                if (!$isImage && !$isVideo) {
                    continue;
                }

                $label = $isImage ? $image->getLabel() : $image->getVideoTitle();
                if (empty($label)) {
                    $label = $name;
                }

                if ($isImage) {
                    if (empty($containersData['mtImageContainer']) || $productImage == $image->getFile()) {
                        $containersData['mtImageContainer'] = $this->toolObj->getMainTemplate([
                            'id' => '-product-'.$id,
                            'img' => $image->getData('large_image_url'),
                            'thumb' => $image->getData('medium_image_url'),
                            'title' => $label,
                            'alt' => $label,
                        ]);
                        $containersData['mtImageContainer'] = '<div>'.$containersData['mtImageContainer'].'</div>';
                        if ($selectorIndex == 0 || $productImage == $image->getFile()) {
                            $defaultContainerId = 'mtImageContainer';
                            $containersData['mtVideoContainer'] = '';
                            $baseIndex = $selectorIndex;
                        }
                    }
                    $selectorsArray[] = $this->toolObj->getSelectorTemplate([
                        'id' => '-product-'.$id,
                        'group' => 'product-page',
                        'img' => $image->getData('large_image_url'),
                        'thumb' => $image->getData('small_image_url'),
                        'medium' => $image->getData('medium_image_url'),
                        'title' => $label,
                        'alt' => $label
                    ]);
                } else {
                    if ($selectorIndex == 0 || $productImage == $image->getFile()) {
                        $defaultContainerId = 'mtVideoContainer';
                        $containersData['mtVideoContainer'] = '<div class="product-video init-video" data-video="' . $image->getVideoUrl() . '"></div>';
                        $baseIndex = $selectorIndex;
                    }

                    $selectorsArray[] =
                        '<a class="video-selector" href="#" onclick="return false" data-video="'.$image->getVideoUrl().'" title="'.$label.'">'.
                        '<img src="'.$image->getData('small_image_url').'" alt="'.$label.'" />'.
                        '</a>';

                }

                $selectorIndex++;
            }

            //NOTE: cooperative mode
            if (isset($data['magic360-html'])) {
                $defaultContainerId = 'mt360Container';
                $containersData['mtVideoContainer'] = '';
                $containersData['mt360Container'] = $data['magic360-html'];
                if (isset($data['magic360-icon'])) {
                    $data['magic360-icon'] =
                        '<a class="m360-selector" title="360" href="#" onclick="return false;">'.
                        '<img class="" src="'.$data['magic360-icon'].'" alt="360" />'.
                        '</a>';
                    array_unshift($selectorsArray, $data['magic360-icon']);
                    $baseIndex = 0;
                }
            }

            foreach ($selectorsArray as $i => &$selector) {
                $class = 'mt-thumb-switcher '.($i == $baseIndex ? 'active-selector ' : '');
                if (preg_match('#(<a(?=\s)[^>]*?(?<=\s)class=")([^"]*+")#i', $selector, $match)) {
                    $selector = str_replace($match[0], $match[1].$class.$match[2], $selector);
                } else {
                    $selector = str_replace('<a ', '<a class="'.$class.'" ', $selector);
                }
            }

            foreach ($containersData as $containerId => $containerHTML) {
                $displayStyle = $defaultContainerId == $containerId ? 'block' : 'none';
                $mainHTML .= "<div id=\"{$containerId}\" style=\"display: {$displayStyle};\">{$containerHTML}</div>";
            }

            if (empty($selectorsArray)) {
                if ($originalBlock) {
                    $this->renderedGalleryHtml[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                }
                return $this;
            }
            $additionalClasses = '';
            $scrollOptions = '';
            if ($scroll = $this->magicToolboxHelper->getScrollObj()) {
                $additionalClasses = $this->toolObj->params->getValue('scroll-extra-styles');
                if (empty($additionalClasses)) {
                    $additionalClasses = 'MagicScroll';
                } else {
                    $additionalClasses = 'MagicScroll '.trim($additionalClasses);
                }

                $scrollOptions = $scroll->params->serialize(false, '', 'magiczoomplus-magicscroll-product');

                //NOTE: disable MagicScroll on page load to start manually
                $scrollOptions = 'autostart:false;'.$scrollOptions;

                if (!empty($scrollOptions)) {
                    $scrollOptions = " data-options=\"{$scrollOptions}\"";
                }
            }
            $selectorMaxWidth = (int)$this->toolObj->params->getValue('selector-max-width');
            $thumbSwitcherOptions = '';
            if (!$isAssociatedProduct) {
                $thumbSwitcherOptions = $this->getThumbSwitcherInitAttribute();
            }

            $layout = $this->toolObj->params->getValue('template');
            $templateData = [
                'data' => [
                    'layout' => $layout,
                    'id' => $id,
                    'mainHTML' => $mainHTML,
                    'selectorsArray' => $selectorsArray,
                    'selectorMaxWidth' => $selectorMaxWidth,
                    'additionalClasses' => $additionalClasses,
                    'scrollOptions' => $scrollOptions,
                    'thumbSwitcherOptions' => $thumbSwitcherOptions,
                ],
            ];
            $templateBlock = \Magento\Framework\App\ObjectManager::getInstance()->create(
                \MagicToolbox\MagicZoomPlus\Block\Template::class,
                $templateData
            );
            $this->renderedGalleryHtml[$id] = $templateBlock->toHtml();
        }
        return $this;
    }

    /**
     * Get placeholder HTML
     *
     * @return string
     */
    public function getPlaceholderHtml()
    {
        static $html = null;
        if ($html === null) {
            $placeholderUrl = $this->_imageHelper->getDefaultPlaceholderUrl('image');
            list($width, $height) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb');
            $html = '<div class="MagicToolboxContainer placeholder"'.$this->getThumbSwitcherInitAttribute().' style="width: '.$width.'px;height: '.$height.'px">'.
                    '<span class="align-helper"></span>'.
                    '<img src="'.$placeholderUrl.'"/>'.
                    '</div>';
        }
        return $html;
    }
}
