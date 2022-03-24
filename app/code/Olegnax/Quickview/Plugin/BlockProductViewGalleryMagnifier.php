<?php

namespace Olegnax\Quickview\Plugin;

use \Magento\Store\Model\ScopeInterface;

class BlockProductViewGalleryMagnifier
{

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
    }

	public function getConfig( $path, $storeCode = null ) {
		return $this->getSystemValue( $path, $storeCode );
	}

	public function getSystemValue( $path, $storeCode = null ) {
		return $this->scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeCode );
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

        if ($this->request->getFullActionName() != 'ox_quickview_catalog_product_view') {
            return $result;
        }

        switch ($name) {
            case "gallery/navdir" :
                $result = 'horizontal';
                break;
            /* Disable the image fullscreen on quickview*/
            case "gallery/allowfullscreen" :
                $result = false;
                break;
        }

        return $result;
    }


}
