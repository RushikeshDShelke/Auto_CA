<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Product;

use Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Categories\CollectionFactory as RestrictedCategoryCollectionFactory;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Product\Media\Video;
use Cminds\Supplierfrontendproductuploader\Helper\Attribute as AttributeHelper;
use Cminds\Supplierfrontendproductuploader\Model\Labels as SupplierLabels;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Management as AttributesByAttributeSet;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory as CategoryTreeFactory;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Downloadable\Model\LinkFactory as DownloadableLinkFactory;
use Magento\Eav\Model\ConfigFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class Creategrouped extends Create
{
    /**
     * ProductLink factory.
     *
     * @var LinkFactory
     */
    protected $productLinkFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Stock item repository.
     *
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * Stock item.
     *
     * @var StockItem
     */
    protected $stockItem;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Creategrouped constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryTreeFactory $categoryTreeFactory
     * @param AttributesByAttributeSet $attributeManagement
     * @param SupplierHelper $supplierHelper
     * @param Http $httpRequest
     * @param AttributeFactory $attributeFactory
     * @param AttributeHelper $attributeHelper
     * @param SupplierLabels $supplierLabels
     * @param CustomerSession $customerSession
     * @param ConfigFactory $configFactory
     * @param DownloadableLinkFactory $downloadableLinkFactory
     * @param Video $video
     * @param StockStateInterface $stockState
     * @param StockItemRepository $stockItemRepository
     * @param LinkFactory $linkFactory
     * @param StockItem $stockItem
     * @param ResourceConnection $resourceConnection
     * @param DirectoryList $directoryList
     * @param RestrictedCategoryCollectionFactory $restrictedCategoryCollectionFactory
     * @param Price $priceHelper
     * @param ProductUploaderInventory $productUploaderInventory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryTreeFactory $categoryTreeFactory,
        AttributesByAttributeSet $attributeManagement,
        SupplierHelper $supplierHelper,
        Http $httpRequest,
        AttributeFactory $attributeFactory,
        AttributeHelper $attributeHelper,
        SupplierLabels $supplierLabels,
        CustomerSession $customerSession,
        ConfigFactory $configFactory,
        DownloadableLinkFactory $downloadableLinkFactory,
        Video $video,
        StockStateInterface $stockState,
        StockItemRepository $stockItemRepository,
        LinkFactory $linkFactory,
        StockItem $stockItem,
        ResourceConnection $resourceConnection,
        DirectoryList $directoryList,
        RestrictedCategoryCollectionFactory $restrictedCategoryCollectionFactory,
        Price $priceHelper,
        ProductUploaderInventory $productUploaderInventory
    ) {
        parent::__construct(
            $context,
            $registry,
            $productFactory,
            $categoryFactory,
            $categoryCollectionFactory,
            $categoryTreeFactory,
            $attributeManagement,
            $supplierHelper,
            $httpRequest,
            $attributeFactory,
            $attributeHelper,
            $supplierLabels,
            $customerSession,
            $configFactory,
            $downloadableLinkFactory,
            $video,
            $stockState,
            $directoryList,
            $restrictedCategoryCollectionFactory,
            $priceHelper,
            $productUploaderInventory
        );
        $this->productLinkFactory = $linkFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->resourceConnection = $resourceConnection;
        $this->stockItem = $stockItem;
    }

    /**
     * Get product by id.
     *
     * @param $productId
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductById($productId)
    {
        $product = $this->productFactory->create()->load($productId);

        return $product;
    }

    /**
     * Get product links instance.
     *
     * @param \Magento\Catalog\Model\Product $product Product model instance
     *
     * @return array
     */
    public function getProductLinks($product)
    {
        $links = $this->productLinkFactory->create()->getCollection()->addFieldToFilter(
            'product_id',
            $product->getId()
        );

        return $links->getData();
    }

    /**
     * Get quantity information for product.
     *
     * @param int $id Product id
     *
     * @return array
     */
    public function getQty($id)
    {
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $select = $connection->select()
            ->from(
                ['o' => $connection->getTableName('catalog_product_link_attribute_decimal')]
            )
            ->where('o.link_id=?', $id)
            ->where('o.product_link_attribute_id = 5');

        return $connection->fetchRow($select);
    }

    /**
     * Get information about stock for product.
     *
     * @param int $productId Product id
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItem($productId)
    {
        $stockItemId = $this->getStockItemIdByProductId($productId);

        return $this->stockItemRepository->get($stockItemId);
    }

    /**
     * Get stock item id by product id.
     *
     * @param int $productId
     *
     * @return int
     */
    public function getStockItemIdByProductId($productId)
    {
        $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $select = $connection->select()
            ->from(
                ['o' => $this->resourceConnection->getTableName('cataloginventory_stock_item')]
            )
            ->where('o.product_id=?', $productId);

        $record = $connection->fetchRow($select);

        return $record['item_id'];
    }
}
