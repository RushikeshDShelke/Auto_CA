<?php
namespace Cminds\Supplierfrontendproductuploader\Model\Product\Media;

use Magento\Framework\Model\AbstractModel;

class Video extends AbstractModel
{
    /**
     * Set video product.
     *
     * @param \Magento\Catalog\Model\Product $product  Product Instance.
     * @param string                         $videoUrl Video URL.
     * @param string                         $mediaUrl Media URL.
     *
     * @return void
     */
    public function setVideo(\Magento\Catalog\Model\Product $product, $videoUrl, $mediaUrl)
    {
        $imageName = $this->uploadVideoThumb($videoUrl, $mediaUrl);

        $video = [
            'position' => '1',
            'media_type' => 'external-video',
            'video_provider' => '',
            'file' => $imageName,
            'value_id' => '',
            'label' => '',
            'disabled' => '0',
            'removed' => '',
            'video_url' => $videoUrl,
            'video_title' => '',
            'video_description' => '',
        ];

        $productMediaGalleryImages = [];
        if (isset($product->getMediaGallery()['images'])) {
            $productMediaGalleryImages = $product->getMediaGallery()['images'];
            $productMediaGalleryImages[count($productMediaGalleryImages)] = $video;
        } else {
            $productMediaGalleryImages[0] = $video;
        }
        $product->setMediaGallery(
            [
                'images' => $productMediaGalleryImages
            ]
        );
    }

    /**
     * Save video thumbnail on disk and return created filename.
     *
     * @param string $videoUrl Video URL.
     * @param string $mediaUrl Media URL.
     *
     * @return string
     */
    protected function uploadVideoThumb($videoUrl, $mediaUrl)
    {
        $partsUrl = parse_url($videoUrl);
        parse_str($partsUrl['query'], $query);
        $imageUrl = 'https://img.youtube.com/vi/'.$query['v'].'/0.jpg';

        $newImageName = 'thumb.jpg';
        $ch = curl_init($imageUrl);
        $fp = fopen($mediaUrl.'/tmp/catalog/product/'.$newImageName, 'a');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $newImageName;
    }
}
