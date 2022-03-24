<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Product;

use Magento\Framework\Model\AbstractModel;

class Configurable extends AbstractModel
{
    private $product;

    public function setProduct($product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getSuperAttributes()
    {
        return $this
            ->getProduct()
            ->getTypeInstance()
            ->getConfigurableAttributesAsArray($this->getProduct());
    }

    public function getConfigurableProductValues()
    {
        $allProducts = $this
            ->getProduct()
            ->getTypeInstance()
            ->getUsedProducts($this->getProduct());

        $products = [];
        foreach ($allProducts as $product) {
            $products[] = $product;
        }

        $configurableAttributeCollection = $this
            ->getProduct()
            ->getTypeInstance()
            ->getConfigurableAttributes($this->getProduct());
        $configurableProductsData = [];

        foreach ($products as $product) {
            foreach ($configurableAttributeCollection as $attribute) {
                $configurableProductsData[$product->getId()][] = [
                    'attribute_id' => $attribute
                        ->getProductAttribute()
                        ->getAttributeId(),
                    'value_index' => $product->getData(
                        $attribute
                            ->getProductAttribute()
                            ->getAttributeCode()
                    ),
                    'is_percent' => '0',
                ];
            }
        }

        return $configurableProductsData;
    }

    public function getUsedValueIds()
    {
        $values = $this->getConfigurableProductValues();
        $preparedValues = [];

        foreach ($values as $value) {
            foreach ($value as $v) {
                $preparedValues[] = $v['value_index'];
            }
        }

        return $preparedValues;
    }

    public function isValueUsed($value_id)
    {
        return in_array($value_id, $this->getUsedValueIds());
    }

    public function getPricingValue($data)
    {
        $usedValues = $this
            ->getProduct()
            ->getTypeInstance(true)
            ->getConfigurableAttributesAsArray($this->getProduct());

        return $this->_findValue($usedValues, $data);
    }

    private function _findValue($usedValues, $attribute_value_id)
    {
        foreach ($usedValues as $usedValue) {
            foreach ($usedValue['values'] as $value) {
                if ($value['value_index'] == $attribute_value_id) {
                    return $value['pricing_value'];
                }
            }
        }
    }
}
