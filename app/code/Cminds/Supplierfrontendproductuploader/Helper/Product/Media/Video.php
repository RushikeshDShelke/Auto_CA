<?php

namespace Cminds\Supplierfrontendproductuploader\Helper\Product\Media;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Video extends AbstractHelper
{

    /**
     * Object constructor.
     *
     * @param Context $context Context object.
     */
    public function __construct(
        Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();

        parent::__construct($context);
    }

    /**
     * Check allowed supplier to  upload videos.
     *
     * @return bool
     */
    public function canAddVideos()
    {
        $allow = $this->scopeConfig->getValue(
            'products_settings/adding_products/allow_suppliers_upload_videos'
        );

        return (bool) $allow;
    }

    /**
     * Get product video url.
     *
     * @param \Magento\Catalog\Model\Product $product Product object instance.
     *
     * @return string
     */
    public function getVideoUrl($product)
    {
        $videoUrl = '';
        $mediaGallery = $product->getMediaGalleryImages();

        foreach ($mediaGallery as $media) {
            if (!empty($media->getVideoUrl())) {
                $videoUrl = $media->getVideoUrl();
            }
        }

        return $videoUrl;
    }
}
