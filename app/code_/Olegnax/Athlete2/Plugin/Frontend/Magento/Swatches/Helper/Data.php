<?php


declare(strict_types=1);

namespace Olegnax\Athlete2\Plugin\Frontend\Magento\Swatches\Helper;

use Closure;
use Exception;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Helper\Data as MagentoData;
use Olegnax\Athlete2\Helper\Helper;
use Psr\Log\LoggerInterface;

class Data
{

    const XML_PRODUCT_IMAGE_WIDTH = 'athlete2_settings/product_images/product_image_width';
    const XML_PRODUCT_IMAGE_HEIGHT = 'athlete2_settings/product_images/product_image_height';
    const XML_IMAGE_WIDTH = 'athlete2_settings/product_images/listing_image_width';
    const XML_IMAGE_HEIGHT = 'athlete2_settings/product_images/listing_image_height';
    /**
     * @var ImageHelper
     */
    protected $imageHelper;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ImageHelper $imageHelper,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->imageHelper = $imageHelper;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function aroundGetProductMediaGallery(
        MagentoData $subject,
        Closure $proceed,
        ModelProduct $product
    ) {
        $result = $proceed($product);
        if (!$this->getConfig(Helper::XML_ENABLED)) {
            return $result;
        }

        $mediaGallery = $product->getMediaGalleryEntries();
        foreach ($mediaGallery as $mediaEntry) {
            if ($mediaEntry->isDisabled() || !isset($result['gallery'][$mediaEntry->getId()])) {
                continue;
            }
            $result['gallery'][$mediaEntry->getId()]['isHover'] = $this->isHoverImage($mediaEntry);
        }

        $size = [$this->getConfig(self::XML_IMAGE_WIDTH), $this->getConfig(self::XML_IMAGE_HEIGHT)];
        $size = array_map('trim', $size);
        $size = array_filter($size);

        $size2 = [$this->getConfig(self::XML_IMAGE_WIDTH), $this->getConfig(self::XML_IMAGE_HEIGHT)];
        $size2 = array_map('trim', $size2);
        $size2 = array_filter($size2);

        if (empty($size) && empty($size2)) {
            return $result;
        }

        $baseImage = null;
        /** @var ProductAttributeMediaGalleryEntryInterface $mediaEntry */
        foreach ($mediaGallery as $mediaEntry) {
            if ($mediaEntry->isDisabled()) {
                continue;
            }
            if (!$baseImage || $this->isMainImage($mediaEntry)) {
                $baseImage = $mediaEntry;
            }

            if (!empty($size)) {
                try {
                    $imageUrl = $this->getUrlResizedImage(
                        $product,
                        $mediaEntry->getFile(),
                        'product_page_image_medium',
                        $size
                    );
                    $result['gallery'][$mediaEntry->getId()]['medium_resized'] = $imageUrl;
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            if (!empty($size2)) {
                try {
                    $imageUrl = $this->getUrlResizedImage(
                        $product,
                        $mediaEntry->getFile(),
                        'product_page_image_medium',
                        $size2
                    );
                    $result['gallery'][$mediaEntry->getId()]['medium'] = $imageUrl;
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }

        if (!$baseImage) {
            return [];
        }
        if (!empty($size)) {
            try {
                $imageUrl = $this->getUrlResizedImage(
                    $product,
                    $baseImage->getFile(),
                    'product_swatch_image_medium',
                    $size
                );
                $result['medium_resized'] = $imageUrl;
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        if (!empty($size2)) {
            try {
                $imageUrl = $this->getUrlResizedImage(
                    $product,
                    $baseImage->getFile(),
                    'product_page_image_medium',
                    $size2
                );
                $result['medium'] = $imageUrl;
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * Checks if image is main image in gallery
     *
     * @param ProductAttributeMediaGalleryEntryInterface $mediaEntry
     * @return bool
     */
    private function isMainImage(ProductAttributeMediaGalleryEntryInterface $mediaEntry): bool
    {
        return in_array('image', $mediaEntry->getTypes(), true);
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
        return $this->imageHelper->init($product, $imageId, $properties)->setImageFile($imageFile);
    }

    private function isHoverImage(ProductAttributeMediaGalleryEntryInterface $mediaEntry): bool
    {
        return in_array('img_hover', $mediaEntry->getTypes(), true);
    }
}