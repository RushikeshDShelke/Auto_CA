<?php

/**
 * Olegnax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_ProductSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductSlider\Block;

use DateTime;
use Exception;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

class BestSellerProducts extends ProductsByIds
{
    /**
     * @var ProductFactory
     */
    protected $product;

    /**
     * BestSellerProducts constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param Context $httpContext
     * @param Data $urlHelper
     * @param ProductFactory $product
     * @param array $data
     * @param LayoutFactory|null $layoutFactory
     * @param Json|null $json
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        Context $httpContext,
        Data $urlHelper,
        ProductFactory $product,
        array $data = [],
        LayoutFactory $layoutFactory = null,
        Json $json = null
    ) {
        $this->product = $product;
        parent::__construct($context, $productCollectionFactory, $catalogProductVisibility, $httpContext, $urlHelper,
            $data, $layoutFactory, $json);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLoadedProductIds()
    {
        $productIds = [];
        /** @var Collection $collection */
        $collection = $this->_loadObject(CollectionFactory::class)->create();
        $period = $this->getData('period');
        if ($period) {
            $collection
                ->addFieldToFilter(
                    'period',
                    [
                        'date' => true,
                        'from' => $this->getDateAgo($period),
                    ]
                );
            if ('month' == $period) {
                $collection->setPeriod('monthly');
            }
        }
        if (method_exists($collection, 'addStoreRestrictions')) {
            $collection->addStoreRestrictions($this->getStoreId());
        }

        $bestsellers = $collection
            ->setOrder('MIN(rating_pos)', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->setPageSize(200);

        $productsCount = $this->getProductsCount();
        foreach ($bestsellers as $_product) {
            $id = $_product->getProductId();
            if (!in_array($id, $productIds) && $this->categoryStatus($id) && $this->stockStatus($id)) {
                $productIds[] = $id;
            }
            if ($productsCount && count($productIds) >= $productsCount) {
                break;
            }
        }

        return $productIds;
    }

    /**
     * @param string $period
     * @param int $countPeriod
     * @return int
     */
    protected function getDateAgo($period = 'year', $countPeriod = 1)
    {
        if (!in_array($period, ['year', 'month', 'day'])) {
            $period = 'year';
        }

        return (new DateTime())
            ->modify('-' . $countPeriod . ' ' . $period)
            ->setTime(0, 0, 0)
            ->getTimestamp();
    }

    /**
     * @param $id
     * @return bool
     */
    protected function categoryStatus($id)
    {
        $categoryIds = $this->getCategoryIds();
        if (!empty($categoryIds)) {
            try {
                $productCategoryIds = $this->product->create()->load($id)->getCategoryIds();
                return 0 < count(array_intersect($categoryIds, $productCategoryIds));
            } catch (Exception $exception) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array|mixed|null
     */
    protected function getCategoryIds()
    {
        $categoryIds = $this->getData('category_ids');
        if (!is_array($categoryIds)) {
            $categoryIds = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
            $categoryIds = array_map('intval', $categoryIds);
            $categoryIds = array_filter($categoryIds);
            $categoryIds = array_unique($categoryIds);
            $this->setData('category_ids', $categoryIds);
        }

        return $this->getData('category_ids');
    }

    /**
     * @param $id
     * @return bool|int
     */
    protected function stockStatus($id)
    {
        if ($this->_scopeConfig->getValue(
                Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                ScopeInterface::SCOPE_STORE
            ) &&
            $this->getShowInStock()) {
            try {
                return $this->stockRegistry->getProductStockStatus($id);
            } catch (Exception $exception) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $newval
     * @return array
     */
    public function getCacheKeyInfo($newval = [])
    {
        return parent::getCacheKeyInfo([
            'OLEGNAX_PRODUCTSLIDER_BESTSELLER_PRODUCTS_LIST_WIDGET',
            implode(',', $this->getCategoryIds()),
            $this->getData('period'),
        ]);
    }

}
