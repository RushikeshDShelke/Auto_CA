<?php

/**
 * Athlete2 Theme
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
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class PrevNextProduct extends AbstractHelper
{
    /**
     *
     * @var ObjectManager
     */
    public $_objectManager;
    /**
     *
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     *
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;
    /**
     *
     * @var ResourceConnection
     */
    protected $_resource;
    /**
     *
     * @var Url
     */
    protected $_catalogUrl;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * PrevNextProduct constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ProductRepositoryInterface $productRepository
     * @param Url $catalogUrl
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ProductRepositoryInterface $productRepository,
        Url $catalogUrl,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_productRepository = $productRepository;
        $this->_resource = $resource;
        $this->_catalogUrl = $catalogUrl;
        $this->_storeManager = $storeManager;
        $this->_objectManager = ObjectManager::getInstance();

        parent::__construct($context);
    }

    /**
     * @return bool|ProductInterface|Product
     * @throws NoSuchEntityException
     */
    public function getNextProduct()
    {
        return $this->getSiblingProduct();
    }

    /**
     * @param int $index
     * @return bool|ProductInterface|Product
     * @throws NoSuchEntityException
     */
    protected function getSiblingProduct($index = 1)
    {
        $product = $this->_coreRegistry->registry('current_product');
        if (!$product) {
            return false;
        }
        $productId = $product->getId();
        $category = $this->_coreRegistry->registry('current_category');

        if (!$category) {
            /** @var Product $product */
            $product = $this->_objectManager->create(Product::class)->load($productId);
            /** @var Category $category */
            $category = $product->getCategory();
            if (!$category) {
                foreach ($product->getCategoryCollection() as $parent_cat) {
                    $category = $parent_cat;
                }
            }
        }

        if ($category) {
            $productIds = array_keys($this->getProductsPosition($category));
            $productPositions = array_flip($productIds);
            if (array_key_exists($productId, $productPositions)) {
                $newProductPosition = $productPositions[$productId] + $index;

                if (array_key_exists($newProductPosition, $productIds)) {
                    $productId = $productIds[$newProductPosition];

                    $product = $this->_productRepository->getById($productId);
                    $product->setCategoryId($category->getId());

                    if ($productId = $product->getId()) {
                        $urlDatas = $this->getRewriteByProductStore([
                            $productId => $category->getStoreId()
                        ]);

                        if (array_key_exists($productId, $urlDatas)) {
                            $urlData = $urlDatas[$productId];
                            $product->setUrlDataObject(new DataObject($urlData));
                        }

                        return $product;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Category $category
     * @return array
     */
    protected function getProductsPosition($category)
    {
        $connection = $this->_resource->getConnection();
        $select = $connection->select()->from(
            $this->_resource->getTableName('catalog_category_product_index'),
            ['product_id', 'position']
        )->where(
            'category_id = :category_id'
        )->where(
            'store_id = :store_id'
        )->order('position', 'ASC');
        $bind = [
            'category_id' => (int)$category->getId(),
            'store_id' => $category->getStoreId(),
        ];

        return $connection->fetchPairs($select, $bind);
    }

    public function getRewriteByProductStore(array $products)
    {
        $result = [];

        if (empty($products)) {
            return $result;
        }
        $connection = $this->_catalogUrl->getConnection();

        $storesProducts = [];
        foreach ($products as $productId => $storeId) {
            $storesProducts[$storeId][] = $productId;
        }
        /** @var TableMaintainer $tableMaintainer */
        $tableMaintainer = ObjectManager::getInstance()->get(TableMaintainer::class);

        foreach ($storesProducts as $storeId => $productIds) {
            $select = $connection->select()->from(
                ['i' => $tableMaintainer->getMainTable($storeId)],
                ['product_id', 'store_id', 'visibility']
            )->joinLeft(
                ['u' => $this->_catalogUrl->getMainTable()],
                'i.product_id = u.entity_id AND i.store_id = u.store_id'
                . ' AND u.entity_type = "' . ProductUrlRewriteGenerator::ENTITY_TYPE . '"',
                ['request_path']
            )->joinLeft(
                ['r' => $this->_catalogUrl->getTable('catalog_url_rewrite_product_category')],
                'u.url_rewrite_id = r.url_rewrite_id AND r.category_id is NULL',
                []
            );

            $bind = [];
            foreach ($productIds as $productId) {
                $catId = $this->_storeManager->getStore($storeId)->getRootCategoryId();
                $productBind = 'product_id' . $productId;
                $storeBind = 'store_id' . $storeId;
                $catBind = 'category_id' . $catId;
                $bindArray = [
                    'i.product_id = :' . $productBind,
                    'i.store_id = :' . $storeBind,
                    'i.category_id = :' . $catBind,
                    'u.metadata is Null',
                ];
                $cond = '(' . implode(' AND ', $bindArray) . ')';
                $bind[$productBind] = $productId;
                $bind[$storeBind] = $storeId;
                $bind[$catBind] = $catId;
                $select->orWhere($cond);
            }

            $rowSet = $connection->fetchAll($select, $bind);
            foreach ($rowSet as $row) {
                $result[$row['product_id']] = [
                    'store_id' => $row['store_id'],
                    'visibility' => $row['visibility'],
                    'url_rewrite' => $row['request_path'],
                ];
            }
        }

        return $result;
    }

    /**
     * @return bool|ProductInterface|Product
     * @throws NoSuchEntityException
     */
    public function getPreviousProduct()
    {
        return $this->getSiblingProduct(-1);
    }

}
