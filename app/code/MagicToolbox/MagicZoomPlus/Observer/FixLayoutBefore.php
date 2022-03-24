<?php

namespace MagicToolbox\MagicZoomPlus\Observer;

/**
 * MagicZoomPlus Observer
 *
 */
class FixLayoutBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    protected $magicToolboxHelper = null;

    /**
     * Constructor
     *
     * @param \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
     */
    public function __construct(
        \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
    }

    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        /** @var \Magento\Framework\View\Layout\Element $layoutXMLElement */
        $layoutXMLElement = $layout->getNode(null);

        //NOTE: for cases when Magento_Swatches module is not installed or disabled
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);
        if (!$moduleManager->isEnabled('Magento_Swatches')) {
            $pathes = [
                //NOTE: product page
                '/layout/body/referenceBlock[@name="product.info.options.wrapper"]/block[@class="MagicToolbox\MagicZoomPlus\Block\Product\Renderer\Configurable"]',
                //NOTE: category page
                '/layout/body/referenceBlock[@name="category.product.type.details.renderers"]/block[@name="configurable.magiczoomplus"]',
            ];
            foreach ($pathes as $searchPath) {
                $nodes = $layoutXMLElement->xpath($searchPath);
                if ($nodes) {
                    foreach ($nodes as $node) {
                        $node->unsetSelf();
                    }
                }
            }
        }

        $pathes = [
            //NOTE: product page media block
            '/layout/body/referenceContainer[@name="product.info.media"]' => 'block[@name="product.info.media.magiczoomplus"]',
            //NOTE: product page layout instruction
            '/layout/body/move[@element="product.info.media.video"]' => 'self::*[@destination="product.info.media"]',
            //NOTE: product page configurable options and swatches blocks
            '/layout/body/referenceBlock[@name="product.info.options.wrapper"]' => 'block[@class="MagicToolbox\MagicZoomPlus\Block\Product\View\Type\Configurable"]',
            //NOTE: category page configurable (swatches) renderer block
            '/layout/body/referenceBlock[@name="category.product.type.details.renderers"]' => 'block[@name="configurable.magiczoomplus"]',
            //NOTE: container for headers
            '/layout/body/referenceBlock[@name="head.additional"]' => 'container[@name="head.additional.magictoolbox"]',
        ];

        $magiczoomplus = $this->magicToolboxHelper->getToolObj();
        $isDisabled = $magiczoomplus->params->checkValue('enable-effect', 'No', 'product');

        foreach ($pathes as $searchPath => $checkPath) {
            $nodes = $layoutXMLElement->xpath($searchPath);
            if ($nodes) {
                foreach ($nodes as $node) {
                    if ($node->xpath($checkPath)) {
                        //NOTE: to remove product page options blocks if effect is disabled
                        if ($isDisabled && 'block[@class="MagicToolbox\MagicZoomPlus\Block\Product\View\Type\Configurable"]' == $checkPath) {
                            $node->unsetSelf();
                            continue;
                        }
                        $body = $layoutXMLElement->addChild('body');
                        $body->appendChild($node);
                        $node->unsetSelf();
                    }
                }
            }
        }

        return $this;
    }
}
