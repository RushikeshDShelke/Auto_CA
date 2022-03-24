<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Plugin\Product\View;

class GalleryPlugin
{
    protected $helperWebp;

    public function __construct(
        \Jajuma\WebpImages\Helper\Data $webpImagesHelper
    ) {
        $this->helperWebp = $webpImagesHelper;
    }

    public function afterGetGalleryImagesJson(\Magento\Catalog\Block\Product\View\Gallery $subject, $result)
    {

        $newImagesItems = [];
        $imagesItems = json_decode($result);

        foreach ($imagesItems as $itemImage) {
            $thumbImage = $itemImage->thumb;
            $imgImage = $itemImage->img;
            $fullImage = $itemImage->full;
            $webpThumbImageUrl = $this->helperWebp->convert($thumbImage);
            $itemImage->thumb_webp = $webpThumbImageUrl;
            $webpImgImageUrl = $this->helperWebp->convert($imgImage);
            $itemImage->img_webp = $webpImgImageUrl;
            $webpFullImageUrl = $this->helperWebp->convert($fullImage);
            $itemImage->full_webp = $webpFullImageUrl;
            $newImagesItems[] = $itemImage;
        }

        return json_encode($newImagesItems);
    }
}