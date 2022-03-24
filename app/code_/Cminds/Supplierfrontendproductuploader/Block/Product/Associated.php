<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Product;

use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Product\Media\Video;
use Cminds\Supplierfrontendproductuploader\Helper\Attribute as AttributeHelper;
use Cminds\Supplierfrontendproductuploader\Model\Labels as SupplierLabels;
use Cminds\Supplierfrontendproductuploader\Model\Product\Configurable as CmindsConfigurable;
use Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Categories\CollectionFactory
    as RestrictedCategoryCollectionFactory;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Management as AttributeManagement;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory as CategoryTreeFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Downloadable\Model\LinkFactory as DownloadableLinkFactory;
use Magento\Eav\Model\ConfigFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class Associated extends Create
{
    /**
     * Cached Configurable Product.
     *
     * @var ProductInterface
     */
    private $configurableProduct;

    /**
     * configurable Product Type Object.
     *
     * @var Configurable
     */
    private $configurable;

    /**
     * Cminds Configurable Product Object.
     *
     * @var CmindsConfigurable
     */
    private $cmindsConfigurable;

    /**
     * Cached Configurable Product.
     *
     * @var ProductInterface
     */
    private $product;

    /**
     * Associated constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryTreeFactory $categoryTreeFactory
     * @param AttributeManagement $attributeManagement
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
     * @param Configurable $configurable
     * @param CmindsConfigurable $cmindsConfigurable
     * @param DirectoryList $directoryList
     * @param RestrictedCategoryCollectionFactory $restrictedCategoryCollectionFactory
     * @param Price $price
     * @param ProductUploaderInventory $productUploaderInventory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryTreeFactory $categoryTreeFactory,
        AttributeManagement $attributeManagement,
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
        Configurable $configurable,
        CmindsConfigurable $cmindsConfigurable,
        DirectoryList $directoryList,
        RestrictedCategoryCollectionFactory $restrictedCategoryCollectionFactory,
        Price $price,
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
            $price,
            $productUploaderInventory
        );

        $this->configurable = $configurable;
        $this->cmindsConfigurable = $cmindsConfigurable;
    }

    /**
     * Get Cached Configurable Product.
     *
     * @return CmindsConfigurable|ProductInterface
     */
    public function getConfigurableModel()
    {
        if (!$this->configurableProduct) {
            $requestParams = $this->getConfigurable();
            $this->configurableProduct = $this->cmindsConfigurable;
            $this->configurableProduct->setProduct($requestParams);
        }

        return $this->configurableProduct;
    }

    /**
     * Get Attribute Set.
     *
     * @param ProductInterface|null $product
     *
     * @return int
     */
    public function getAttributeSetId($product = null)
    {
        $requestParams = $this->getConfigurable();

        return $requestParams['attribute_set_id'];
    }

    /**
     * Get registered configurable product id.
     *
     * @return int
     */
    public function getConfigurableProductId()
    {
        return $this->registry->registry('product_object_configurable_id');
    }

    /**
     * Get configurable product id.
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getConfigurable()->getId();
    }

    /**
     * Get configurable attributes as array.
     *
     * @param ProductInterface|null $product
     *
     * @return array
     */
    public function getAttributes($product = null)
    {
        $configurableAttributesData = $this
            ->getConfigurable()
            ->getTypeInstance()
            ->getConfigurableAttributesAsArray($this->getConfigurable());

        return $configurableAttributesData;
    }

    /**
     * Get associated products of configurable product.
     *
     * @return ProductInterface[]
     */
    public function getChildrenProducts()
    {
        $childProducts = $this->configurable
            ->getUsedProducts(
                $this->getConfigurable(),
                null
            );

        return $childProducts;
    }

    /**
     * Get cached product.
     *
     * @return Associated|ProductInterface
     */
    public function getConfigurable()
    {
        if (!$this->product) {
            $this->product = $this->productFactory->create()
                ->load($this->getConfigurableProductId());
        }

        return $this->product;
    }

    /**
     * Get associated product ids.
     *
     * @return array
     */
    public function getChildrenProductIds()
    {
        $children = $this->getChildrenProducts();
        $ids = [];

        foreach ($children as $child) {
            $ids[] = $child->getId();
        }

        return $ids;
    }

    /**
     * Get not associated products to the current configurable product.
     *
     * @return Collection
     */
    public function getNotAssociatedProducts()
    {
        $notAssociatedProducts = $this->productFactory->create()
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('creator_id')
            ->addAttributeToFilter('type_id', 'simple')
            ->addAttributeToFilter(
                'attribute_set_id',
                $this->getConfigurable()->getAttributeSetId()
            )
            ->addAttributeToFilter(
                'creator_id',
                $this->supplierHelper->getSupplierId()
            );

        $childrenIds = $this->getChildrenProductIds();
        if (!empty($childrenIds)) {
            $notAssociatedProducts->addAttributeToFilter(
                'entity_id',
                ['nin' => $childrenIds]
            );
        }

        foreach ($notAssociatedProducts as $product) {
            if ($this->areOptionsExists($product)) {
                $notAssociatedProducts->removeItemByKey($product->getId());
            }
        }

        return $notAssociatedProducts;
    }

    /**
     * Check if all configurable attributes of configurable products are defined in the associated product.
     * If associated product has all configurable attributes of configurable product, then return true,
     * in other way - false
     *
     * @param $simpleProduct
     *
     * @return bool
     */
    public function areOptionsExists($simpleProduct)
    {
        $product = $this->productFactory->create()
            ->load($simpleProduct->getId());

        $superAttributes = $this->configurable->getConfigurableAttributeCollection($product);
        $allAttributesCount = count($superAttributes);
        $matchedValuesCount = 0;



        foreach ($superAttributes as $attribute) {
            $simpleProductData = $product->getData($attribute['attribute_code']);

            if ($simpleProductData == null) {
                $matchedValuesCount++;
                continue;
            }

            foreach ($attribute['values'] as $value) {
                if ($value['value_index'] == $simpleProductData
                    || !$simpleProductData
                ) {
                    $matchedValuesCount++;
                }
            }
        }

        return ($matchedValuesCount === $allAttributesCount);
    }

    /**
     *
     *
     * @return Product
     */
    public function getCatalogProductModel()
    {
        return $this->productFactory->create();
    }
}
