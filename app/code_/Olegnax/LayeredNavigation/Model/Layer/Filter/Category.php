<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogSearch\Model\Layer\Filter\Category as FilterCategory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\LayeredNavigation\Helper\Helper;
use Olegnax\LayeredNavigation\Model\Layer\Filter as LayerFilter;

class Category extends FilterCategory
{

    protected $_helper;
    protected $_isFilter = false;
    private $escaper;
    private $dataProvider;
    private $_layerFilter;
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    /**
     * @var array
     */
    private $categorys;

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Escaper $escaper,
        CategoryFactory $categoryDataProviderFactory,
        Helper $helper,
        \Magento\Catalog\Model\Category $category,
        LayerFilter $layerFilter,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );

        $this->escaper = $escaper;
        $this->_helper = $helper;
        $this->_layerFilter = $layerFilter;
        $this->category = $category;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->categorys = [];
    }

    public function apply(RequestInterface $request)
    {
        if (!$this->_helper->isEnabled()) {
            return parent::apply($request);
        }

        $categoryId = $request->getParam($this->_requestVar);
        if (empty($categoryId)) {
            return $this;
        }

        $categoryIds = [];
        if (is_string($categoryId)) {
            $categoryId = explode(',', $categoryId);
        }
        foreach ($categoryId as $key => $id) {
            $this->dataProvider->setCategoryId($id);
            if ($this->dataProvider->isValid()) {
                $category = $this->dataProvider->getCategory();
                if ($request->getParam('id') != $id) {
                    $categoryIds[] = $id;
                    $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $id));
                }
            }
        }

        if (count($categoryIds)) {
            $this->_isFilter = true;
            $productCollection = $this->getLayer()->getProductCollection();
            if (method_exists($productCollection, 'addLayerCategoryFilter')) {
                $productCollection->addLayerCategoryFilter($categoryIds);
            } else {
                $productCollection->addFieldToFilter('category_ids', implode(',', $categoryIds));
            }
        }

        if ($parentCategoryId = $request->getParam('id')) {
            $this->dataProvider->setCategoryId($parentCategoryId);
        }

        return $this;
    }

    public function getIsDisableAjax($filterItem)
    {
        $categoryItem = $this->getCategoryItem($filterItem);
        return $categoryItem->getData('ox_nav_disable_ajax') || !$categoryItem->getIsAnchor();
    }

    private function getCategoryItem($filterItem)
    {
        $value = $filterItem->getValue();
        if (!array_key_exists($value, $this->categorys)) {
            $category = clone $this->category;
            $this->categorys[$value] = $category->load($value);
        }

        return $this->categorys[$value];
    }

    public function getUrlItem($filterItem)
    {
        return $this->getCategoryItem($filterItem)->getUrl();
    }

    protected function _getItemsData()
    {
        if (!$this->_helper->isEnabled()) {
            return parent::_getItemsData();
        }

        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter && method_exists($productCollection, 'getCollectionClone')) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch('category_ids');
        }

        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();

        $collectionSize = $productCollection->getSize();

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                $count = 0;
                if (isset($optionsFacetedData[$category->getId()])) {
                    $count = $optionsFacetedData[$category->getId()]['count'];
                }
                if ($category->getIsActive() &&
                    $this->_layerFilter->isOptionReducesResults(
                        $this,
                        $count,
                        $collectionSize
                    )) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $count
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }
}
