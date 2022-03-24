<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Block;

class Picture extends \Magento\Framework\View\Element\Template
{
    protected $_template = "picture-tag-format.phtml";

    /**
     * @var string
     */
    private $webpImage = '';

    /**
     * @var string
     */
    private $originalImage = '';

    /**
     * @var string
     */
    private $originalTag = '';

    /**
     * @var string
     */
    private $customSrcTag = '';

    /**
     * @var string
     */
    private $customSrcSetTag = '';

    /**
     * @param string $originalImage
     *
     * @return Picture
     */
    public function setOriginalImage($originalImage)
    {
        $this->originalImage = $originalImage;
        return $this;
    }

    public function getOriginalImage()
    {
        return $this->originalImage;
    }

    /**
     * @param string $webpImage
     *
     * @return Picture
     */
    public function setWebpImage($webpImage)
    {
        $this->webpImage = $webpImage;
        return $this;
    }

    public function getWebpImage()
    {
        return $this->webpImage;
    }

    /**
     * @param string $originalTag
     *
     * @return Picture
     */
    public function setOriginalTag($originalTag)
    {
        $this->originalTag = $originalTag;
        return $this;
    }

    public function getOriginalTag()
    {
        return $this->originalTag;
    }

    public function setCustomSrcTag($customSrcTag)
    {
        $this->customSrcTag = $customSrcTag;
        return $this;
    }

    public function getCustomSrcTag()
    {
        return $this->customSrcTag;
    }

    public function setCustomSrcSetTag($customSrcSetTag)
    {
        $this->customSrcSetTag = $customSrcSetTag;
        return $this;
    }

    public function getCustomSrcSetTag()
    {
        return $this->customSrcSetTag;
    }

    /**
     * @return string
     */
    public function getOriginalImageType()
    {
        if (preg_match('/\.(jpg|jpeg)$/i', $this->getOriginalImage())) {
            return 'image/jpg';
        }

        if (preg_match('/\.(png)$/i', $this->getOriginalImage())) {
            return 'image/png';
        }

        return '';
    }

}