<?php

namespace MagicToolbox\MagicZoomPlus\Plugin\Product;

/**
 * Plugin for \Magento\Catalog\Block\Product\ListProduct
 */
class ListProduct
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    protected $magicToolboxHelper = null;

    /**
     * MagicZoomPlus module core class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicZoomPlusModuleCoreClass
     *
     */
    protected $toolObj = null;

    /**
     * Disable flag
     * @var bool
     */
    protected $isDisabled = true;

    /**
     * @param \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
     */
    public function __construct(
        \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
        $this->toolObj = $this->magicToolboxHelper->getToolObj();
        $this->toolObj->params->setProfile('category');
        $this->isDisabled = !$this->toolObj->params->checkValue('enable-effect', 'Yes', 'category');
    }

    /**
     * Produce and return block's html output
     *
     * @param \Magento\Catalog\Block\Product\ListProduct $listProductBlock
     * @param string $html
     * @return string
     */
    public function afterToHtml(\Magento\Catalog\Block\Product\ListProduct $listProductBlock, $html)
    {
        if ($this->isDisabled) {
            return $html;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);

        //NOTE: do not apply for product listing with not standard renderer (#148451)
        $isAnotherRendererAvailable = $this->magicToolboxHelper->getAnotherRenderer();
        if ($moduleManager->isEnabled('Magento_Swatches') && !$isAnotherRendererAvailable) {
            return $html;
        }

        $this->magicToolboxHelper->setListProductBlock($listProductBlock);
        $productCollection = $listProductBlock->getLoadedProductCollection();
        $patternBegin = '<a(?=\s)[^>]+?(?<=\s)href="';
        $patternEnd = '"[^>]++>.*?</a>';

        foreach ($productCollection as $product) {

            $_html = $this->magicToolboxHelper->getHtmlData($product);

            if (empty($_html)) {
                continue;
            }

            $url = preg_quote($product->getProductUrl(), '#');
            $html = preg_replace("#{$patternBegin}{$url}{$patternEnd}#s", $_html, $html, 1);

        }

        return $html;
    }
}
