<?php


namespace Olegnax\Athlete2\Plugin\ConfigurableProduct\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Athlete2\Helper\ProductImage;

class Data
{
    const XML_IMAGE_WIDTH = 'athlete2_settings/product_images/product_image_width';
    const XML_IMAGE_HEIGHT = 'athlete2_settings/product_images/product_image_height';
    /**
     *
     * @var ObjectManager
     */
    public $_objectManager;

    public function aroundGetGalleryImages($subject, $proceed, ProductInterface $product)
    {
        if (!$this->getConfig(Helper::XML_ENABLED)) {
            return $proceed($product);
        }
        $size = [$this->getConfig(self::XML_IMAGE_WIDTH), $this->getConfig(self::XML_IMAGE_HEIGHT)];
        $size = array_map('trim', $size);
        $size = array_filter($size);

        if (!empty($size)) {
            $images = $product->getMediaGalleryImages();
            if ($images instanceof Collection) {
                $galleryImagesConfig = [
                    'small_image_url' => 'product_page_image_small',
                    'medium_image_url' => 'product_page_image_medium',
                    'large_image_url' => 'product_page_image_large',
                ];
                /** @var $image \Magento\Catalog\Model\Product\Image */
                foreach ($images as $image) {
                    foreach ($galleryImagesConfig as $dataObjectKey => $imageId) {
                        $imageUrl = '';
                        if ('medium_image_url' == $dataObjectKey) {
                            try {
                                $imageUrl = $this->getUrlResizedImage($product, $image->getFile(), $imageId, $size);
                            } catch (\Exception $e) {
                                $this->_loadObject(\Psr\Log\LoggerInterface::class)->error($e->getMessage());
                                $imageUrl = $this->_loadObject(UrlBuilder::class)->getUrl($image->getFile(), $imageId);
                            }
                        } else {
                            $imageUrl = $this->_loadObject(UrlBuilder::class)->getUrl($image->getFile(), $imageId);
                        }

                        $image->setData($dataObjectKey, $imageUrl);
                    }
                }
            }

            return $images;
        }
        return $proceed($product);
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
        return ObjectManager::getInstance()->get($object);
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
        return $this->_loadObject(ImageHelper::class)->init($product, $imageId, $properties)->setImageFile($imageFile);
    }
}
