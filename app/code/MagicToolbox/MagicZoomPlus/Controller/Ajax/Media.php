<?php

namespace MagicToolbox\MagicZoomPlus\Controller\Ajax;

/**
 * Ajax media controller
 *
 */
class Media extends \Magento\Swatches\Controller\Ajax\Media
{

    /**
     * Get product media
     *
     * @return string
     */
    public function execute()
    {
        $result = [];

        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $currentProduct = $this->productModelFactory->create()->load($productId);
            $isConfigurable = ($currentProduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
            $attributes = (array)$this->getRequest()->getParam('attributes');

            if ($isConfigurable && method_exists($this, 'getProductVariationWithMedia')) {
                $product = null;
                if (!empty($attributes)) {
                    $product = $this->getProductVariationWithMedia($currentProduct, $attributes);
                }

                if ($product && $product->getImage() && $product->getImage() != 'no_selection') {
                    $currentProduct = $product;
                    $product = null;
                }
            }

            $result['variantProductId'] = $currentProduct->getId();
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
