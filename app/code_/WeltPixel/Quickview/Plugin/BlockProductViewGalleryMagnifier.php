<?php

namespace WeltPixel\Quickview\Plugin;

class BlockProductViewGalleryMagnifier
{

    const XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_ENABLED = 'weltpixel_quickview/general/enable_zoom';
    const XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_TOP = 'weltpixel_quickview/general/zoom_top';
    const XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_LEFT = 'weltpixel_quickview/general/zoom_left';
    const XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_WIDTH = 'weltpixel_quickview/general/zoom_width';
    const XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_HEIGHT = 'weltpixel_quickview/general/zoom_height';
    const XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_EVENTTYPE = 'weltpixel_quickview/general/zoom_eventtype';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var  \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     *
     * @var  \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     *
     * @var  \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * ResultPage constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Framework\Json\EncoderInterface $jsonEncoder,
                                \Magento\Framework\Json\DecoderInterface $jsonDecoder)
    {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }


    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param $result
     * @return mixed
     */
    public function afterGetMagnifier(
        \Magento\Catalog\Block\Product\View\Gallery $subject, $result
    )
    {
        if ($this->request->getFullActionName() != 'weltpixel_quickview_catalog_product_view') {
            return $result;
        }
        $result = $this->jsonDecoder->decode($result);
        $magnifierEnabled = $this->scopeConfig->getValue(self::XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_ENABLED,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $magnifierTop = $this->scopeConfig->getValue(self::XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_TOP,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $magnifierLeft = $this->scopeConfig->getValue(self::XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_LEFT,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $magnifierWidth = $this->scopeConfig->getValue(self::XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_WIDTH,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $magnifierHeight = $this->scopeConfig->getValue(self::XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_HEIGHT,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $magnifierEventtype = $this->scopeConfig->getValue(self::XML_PATH_WELTPIXEL_QUICKVIEW_MAGNIFIER_EVENTTYPE,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $result['enabled'] = $magnifierEnabled;
        $result['top'] = $magnifierTop;
        $result['left'] = $magnifierLeft;
        $result['width'] = $magnifierWidth;
        $result['height'] = $magnifierHeight;
        $result['eventType'] = $magnifierEventtype;

        return $this->jsonEncoder->encode($result);
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param \Closure $proceed
     * @param string $name
     * @param string|null $module
     * @return string|false
     */
    public function aroundGetVar(
        \Magento\Catalog\Block\Product\View\Gallery $subject,
        \Closure $proceed,
        $name,
        $module = null
    )
    {
        $result = $proceed($name, $module);

        if ($this->request->getFullActionName() != 'weltpixel_quickview_catalog_product_view') {
            return $result;
        }

        switch ($name) {
            case "gallery/navdir" :
                $removeProductImageThumb = $this->scopeConfig->getValue(\WeltPixel\Quickview\Observer\AddUpdateHandlesObserver::XML_PATH_QUICKVIEW_REMOVE_PRODUCT_IMAGE_THUMB,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($removeProductImageThumb) {
                    $result = 'horizontal';
                }
                break;
            /* Disable the image fullscreen on quickview*/
            case "gallery/allowfullscreen" :
                $result = false;
                break;
        }

        return $result;
    }


}
