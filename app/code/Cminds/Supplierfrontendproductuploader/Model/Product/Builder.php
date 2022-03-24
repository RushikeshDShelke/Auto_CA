<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Product;

use Cminds\Supplierfrontendproductuploader\Model\Product\Builder\Type\Configurable as ConfigurableBuilder;
use Magento\Catalog\Api\Data\ProductInterface;

class Builder
{
    /**
     * Product Builder Object.
     *
     * @var ConfigurableBuilder
     */
    private $configurableBuilder;

    /**
     * Builder constructor.
     *
     * @param ConfigurableBuilder $configurableBuilder
     */
    public function __construct(
        ConfigurableBuilder $configurableBuilder
    ) {
        $this->configurableBuilder = $configurableBuilder;
    }

    /**
     * Fill product with configurable attributes.
     *
     * @param ProductInterface $product
     * @param array            $attributes
     *
     * @return ProductInterface
     */
    public function fillProductWithConfigurableAttributes(
        ProductInterface $product,
        array $attributes
    ) {
        return $this->configurableBuilder->fillProductWithConfigurableAttributes(
            $product,
            $attributes
        );
    }
}
