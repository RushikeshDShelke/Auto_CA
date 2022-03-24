<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Olegnax\Athlete2\Plugin\Catalog\Block\Product\View;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Athlete2\Helper\ProductImage;

/**
 * Description of Gallery
 *
 * @author Master
 */
class Gallery
{
    const XML_IMAGE_WIDTH = 'athlete2_settings/product_images/product_image_width';
    const XML_IMAGE_HEIGHT = 'athlete2_settings/product_images/product_image_height';
    /**
     *
     * @var ObjectManager
     */
    public $_objectManager;

    public function afterGetGalleryImages($subject, $result)
    {
        if (!$this->getConfig(Helper::XML_ENABLED)) {
            return $result;
        }
        $size = [$this->getConfig(self::XML_IMAGE_WIDTH), $this->getConfig(self::XML_IMAGE_HEIGHT)];
        $size = array_map('trim', $size);
        $size = array_filter($size);

        if (!empty($size)) {
            $product = $subject->getProduct();
            $images = $product->getMediaGalleryImages();
            if (!$images instanceof Collection) {
                return $images;
            }

            foreach ($images as $image) {
                $galleryImagesConfig = $subject->getGalleryImagesConfig()->getItems();
                foreach ($galleryImagesConfig as $imageConfig) {
                    $dataObjectKey = $imageConfig->getData('data_object_key');
                    $imageUrl = '';
                    $imageId = $imageConfig['image_id'];
                    if ('medium_image_url' == $dataObjectKey) {
                        $imageUrl = $this->getUrlResizedImage($product, $image->getFile(), $imageId, $size);
                    } else {
                        $imageUrl = $this->_loadObject(UrlBuilder::class)->getUrl($image->getFile(), $imageId);
                    }
                    $image->setData($dataObjectKey, $imageUrl);
                }
            }

            return $images;
        }


        return $result;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getScopeConfig()->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    protected function getScopeConfig()
    {
        return $this->_loadObject(ScopeConfigInterface::class);
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    public function getUrlResizedImage(Product $product, $imageFile, $imageId, $size, $properties = [])
    {
        $image = $this->resizeImage($product, $imageFile, $imageId, $size, $properties);
        return $image->getUrl();
    }

    public function resizeImage(Product $product, $imageFile, $imageId, $size, $properties = [])
    {
        $size = $this->prepareSize($size);
        $image = $this->_getImage($product, $imageFile, $imageId, $properties);
        $image->resize($size[0], $size[1]);

        return $image;
    }

    private function prepareSize($size)
    {
        if (is_array($size) && 1 >= count($size)) {
            $size = array_shift($size);
        }
        if (!is_array($size)) {
            $size = [$size, $size];
        }
        $size = array_map('floatval', $size);
        $size = array_map('abs', $size);
        return $size;
    }

    private function _getImage(Product $product, $imageFile, $imageId, $properties = [])
    {
        return $this->_loadObject(Image::class)->init($product, $imageId, $properties)->setImageFile($imageFile);
    }

    protected function getProductHelper()
    {
        return $this->_loadObject(ProductImage::class);
    }
}
