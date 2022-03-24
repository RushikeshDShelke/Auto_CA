<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Plugin;

use Magento\Framework\View\LayoutInterface;
use Jajuma\WebpImages\Block\Picture;

class ReplaceImageTag
{
    protected $helper;

    protected $storeManager;

    public function __construct(
        \Jajuma\WebpImages\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }


    public function afterGetOutput(LayoutInterface $layout, $output)
    {
        if (!$this->helper->isEnabled()) {
            return $output;
        }

        $regex = '/<img([^<]+\s|\s)src=(\"|' . "\')([^<]+\.(png|jpg|jpeg))[^<]+>(?!(<\/pic|\s*<\/pic))/mi";
        if (preg_match_all($regex, $output, $images, PREG_OFFSET_CAPTURE) === false) {
            return $output;
        }
        $accumulatedChange = 0;
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaUrlWithoutBaseUrl = str_replace($baseUrl, '', $mediaUrl);
        $excludeImageAttributes = $this->getExcludeImageAttributes();
        $customSrcSetTag = $this->helper->getCustomSrcSetTag() ? $this->helper->getCustomSrcSetTag() : '';
        foreach ($images[0] as $index => $image) {
            $offset = $image[1] + $accumulatedChange;
            $htmlTag = $images[0][$index][0];
            $imageUrl = $images[3][$index][0];

            /**
             * Skip when image is not from same server
             */
            if (strpos($imageUrl, $mediaUrl) === false && strpos($imageUrl, $mediaUrlWithoutBaseUrl) === false) {
                continue;
            }

            /**
             * Skip when image contains an excluded attribute
             */
            if (preg_match_all($excludeImageAttributes, $htmlTag)) {
                continue;
            }

            $pictureTag = $this->convertImage($imageUrl, $htmlTag, $customSrcSetTag, $layout);

            if (!$pictureTag) {
                continue;
            }

            $output = substr_replace($output, $pictureTag, $offset, strlen($htmlTag));
            $accumulatedChange = $accumulatedChange + (strlen($pictureTag) - strlen($htmlTag));
        }
        return $output;
    }

    /**
     * Get picture tag format
     *
     * @param LayoutInterface $layout
     * @return Picture
     */
    private function getPicture(LayoutInterface $layout)
    {
        /** @var Picture $block */
        $block = $layout->createBlock(Picture::class);
        return $block;
    }

    /**
     * @return string
     */
    private function getExcludeImageAttributes()
    {
        $excludeImageAttributes = $this->helper->getExcludeImageAttribute();
        if ($excludeImageAttributes)
        {
            $excludeImageAttributes = explode(',', $excludeImageAttributes);
            $excludeImageAttributes = array_map('trim', $excludeImageAttributes);
            $excludeImageAttributes = implode(".*|.*", $excludeImageAttributes);
            $excludeImageAttributes = '/(.*data-nowebp=\"true\".*|.*\/media\/captcha\/.*|.*' . $excludeImageAttributes . '.*)/mi';
        } else {
            $excludeImageAttributes = '/(.*data-nowebp=\"true\".*|.*\/media\/captcha\/.*)/mi';
        }

        return $excludeImageAttributes;
    }

    /**
     * @param $imageUrl string
     * @param $htmlTag string
     * @param $customSrcSetTag string
     * @param $layout LayoutInterface
     *
     * @return bool|string
     */
    private function convertImage($imageUrl, $htmlTag, $customSrcSetTag, $layout)
    {
        $lazyload = false;
        if ($customSrcTag = $this->helper->getCustomSrcTag()) {
            $expression = '/('.$customSrcTag.')=(\"|' . "\')([^<]+\.(png|jpg|jpeg))/mU";
            if (preg_match_all($expression, $htmlTag, $match, PREG_OFFSET_CAPTURE)) {
                $lazyload = true;
                $imageUrl = $match[3][0][0];
            }
        }

        $webpUrl = $this->helper->convert($imageUrl);

        /**
         * Skip when extension can not convert the image
         */
        if ($webpUrl === $imageUrl) {
            return false;
        }
        if ($lazyload) {
            $pictureTag = $this->getPicture($layout)
                ->setOriginalImage($imageUrl)
                ->setWebpImage($webpUrl)
                ->setOriginalTag($htmlTag)
                ->setCustomSrcTag($customSrcTag)
                ->setCustomSrcSetTag($customSrcSetTag)
                ->toHtml();
        } else {
            $pictureTag = $this->getPicture($layout)
                ->setOriginalImage($imageUrl)
                ->setWebpImage($webpUrl)
                ->setOriginalTag($htmlTag)
                ->toHtml();
        }

        return $pictureTag;
    }
}