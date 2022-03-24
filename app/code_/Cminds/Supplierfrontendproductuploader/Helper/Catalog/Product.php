<?php

namespace Cminds\Supplierfrontendproductuploader\Helper\Catalog;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Catalog\Helper\Product as CoreProduct;
use Magento\Catalog\Model\Attribute\Config;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\Repository;
use Magento\Catalog\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Cminds\Supplierfrontendproductuploader\Helper\Data\Proxy as DataHelper;

class Product extends CoreProduct
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Session $catalogSession,
        Repository $assetRepo,
        Registry $coreRegistry,
        Config $attributeConfig,
        array $reindexPriceIndexerData,
        array $reindexProductCategoryIndexerData,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        MessageManagerInterface $messageManager,
        DataHelper $data
    ) {
        $this->messageManager = $messageManager;
        $this->helper = $data;

        parent::__construct(
            $context,
            $storeManager,
            $catalogSession,
            $assetRepo,
            $coreRegistry,
            $attributeConfig,
            $reindexPriceIndexerData,
            $reindexProductCategoryIndexerData,
            $productRepository,
            $categoryRepository
        );
    }

    /**
     * Method return true when product can be showed and false on other case.
     *
     * Product can be showed if is visibility and approve by admin.
     * If current customer is product owner he can see product always but with message that it is preview mode.
     *
     * @param ModelProduct $product
     * @param string $where
     *
     * @return bool
     */
    public function canShow($product, $where = 'catalog')
    {
        if (is_int($product)) {
            try {
                $product = $this->productRepository->getById($product);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        } else {
            if (!$product->getId()) {
                return false;
            }
        }

        $showProduct = $product->isVisibleInCatalog()
            && $product->isVisibleInSiteVisibility();

        if (!$showProduct && $product->getCreatorId()
            && $product->getFrontendproductProductStatus() != 1
        ) {
            $ownProduct = ($product->getData('creator_id') === $this->helper->getSupplierId());

            $showProduct = $ownProduct;

            return $showProduct;
        } else {
            return $showProduct;
        }
    }
}
