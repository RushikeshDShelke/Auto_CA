<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Attribute as FilterAttribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\LayeredNavigation\Helper\Helper;
use Olegnax\LayeredNavigation\Model\Layer\Filter as LayerFilter;
use Olegnax\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;

class Attribute extends FilterAttribute
{

    protected $_helper;
    protected $_isFilter = true;
    protected $tagFilter;
    protected $_layerFilter;
    protected $_requestParamVal;
    protected $_resource;
    protected $_originalCollection;

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        StripTags $tagFilter,
        AttributeFactory $filterAttributeFactory,
        Helper $helper,
        LayerFilter $layerFilter,
        array $data = []
    ) {
        $this->_resource = $filterAttributeFactory->create();
        $this->tagFilter = $tagFilter;
        $this->_helper = $helper;
        $this->_layerFilter = $layerFilter;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $tagFilter, $data);
    }

    public function apply(RequestInterface $request)
    {
        if (!$this->_helper->isEnabled()) {
            return parent::apply($request);
        }

        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue)) {
            $this->_isFilter = false;

            return $this;
        }
        $this->_requestParamVal = $this->_requestVar;
        $attributeValue = explode(',', $attributeValue);
        if ($this->shouldAddState()) {
            $state = $this->getLayer()->getState();
            foreach ($attributeValue as $value) {
                $label = $this->getOptionText($value);
                $state->addFilter($this->_createItem($label, $value));
            }
        }
        $attribute = $this->getAttributeModel();

        if (!$this->isMultiselect($attribute->getId()) && count($attributeValue) > 1) {
            $attributeValue = array_slice($attributeValue, 0, 1);
        }

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        if (!$this->_originalCollection && method_exists($productCollection, 'getCollectionClone')) {
            $this->_originalCollection = $productCollection->getCollectionClone();
        }
        if (count($attributeValue) > 1) {
            if ($this->_helper->isElasticSearchEngine()) {
                $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
            } else {
                $productCollection->addFieldToFilter($attribute->getAttributeCode(), ['in' => $attributeValue]);
            }
        } else {
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValue[0]);
        }

        return $this;
    }

    protected function isMultiselect($attrId = null)
    {
        return true;
    }

    protected function _getResource()
    {
        return $this->_resource;
    }

    protected function _getItemsData()
    {
        if (!$this->_helper->isEnabled()) {
            return parent::_getItemsData();
        }

        $attribute = $this->getAttributeModel();

        if (!$this->isMultiselect($attribute->getId()) && $this->_isFilter || !$this->isVisibleWhenSelected()) {
            return [];
        }

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter && $this->_layerFilter->isMainFilter($this) && method_exists($productCollection, 'getCollectionClone')) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch($attribute->getAttributeCode());
        }

        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());

        if (count($optionsFacetedData) === 0 &&
            $this->getAttributeIsFilterable($attribute) !== static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS
        ) {
            return $this->itemDataBuilder->build();
        }

        $productSize = $productCollection->getSize();

        $itemData = [];
        $checkCount = false;
        $options = $attribute->getFrontend()->getSelectOptions();
        $counter = false;

        foreach ($options as $option) {
            $sorted = false;
            if (empty($option['value'])) {
                continue;
            }

            $value = $option['value'];

            if ($counter) {
                $count = isset($counter[$value]) ? (int)$counter[$value] : 0;
            } else {
                $count = isset($optionsFacetedData[$value]['count']) ? (int)$optionsFacetedData[$value]['count'] : 0;
            }

            // Check filter type
            if ($this->getAttributeIsFilterable($attribute) == static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS &&
                (!$this->_layerFilter->isOptionReducesResults($this, $count, $productSize)) && $count == 0) {
                continue;
            }

            if ($count > 0) {
                $checkCount = true;
            }

            $itemData[] = [
                'label' => $this->tagFilter->filter($option['label']),
                'value' => $value,
                'count' => $count
            ];
        }


        if ($checkCount) {
            foreach ($itemData as $item) {
                $this->itemDataBuilder->addItemData($item['label'], $item['value'], $item['count']);
            }
        }

        return $this->itemDataBuilder->build();
    }

    protected function _compareAz($a, $b)
    {
        return strcmp($a["label"], $b["label"]);
    }

    /**
     * @return bool
     */
    protected function shouldAddState()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function isVisibleWhenSelected()
    {
        return true;
    }
}
