<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
    as AttributeCollection;

class Set
{
    protected $_product;
    protected $_attributeCollection;

    public function __construct(
        Product $product,
        AttributeCollection $attributeCollection
    ) {
        $this->_product = $product;
        $this->_attributeCollection = $attributeCollection;
    }

    public function toOptionArray()
    {
        $entityType = $this->_product->getResource()->getTypeId();
        $collection = $this->_attributeCollection->setEntityTypeFilter($entityType);
        $allSet = [];

        foreach ($collection as $attributeSet) {
            $allSet[] = [
                'value' => $attributeSet->getAttributeSetId(),
                'label' => $attributeSet->getAttributeSetName(),
            ];
        }

        return $allSet;
    }
}
