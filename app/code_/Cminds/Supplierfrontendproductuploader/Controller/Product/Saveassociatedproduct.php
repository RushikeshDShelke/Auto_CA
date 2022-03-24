<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Model\Product as CmindsModelProduct;
use Cminds\Supplierfrontendproductuploader\Model\Product\Configurable as CmindsConfigurableProductModel;
use Cminds\Supplierfrontendproductuploader\Model\Product\Builder\Type\Configurable as ConfigurableBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Catalog\Model\Product\Visibility;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class Saveassociatedproduct extends AbstractController
{
    /**
     * Transaction Object.
     *
     * @var Transaction
     */
    private $transaction;

    /**
     * Product Entity Object.
     *
     * @var CatalogProduct
     */
    private $catalogProduct;

    /**
     * Configurable Product Type Object.
     *
     * @var Configurable
     */
    private $configurable;

    /**
     * Cminds Configurable Proiduct Type Object.
     *
     * @var CmindsConfigurableProductModel
     */
    private $cmindsConfigurableModel;

    /**
     * Product Factory.
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Configurable Product Type Factory Object/
     *
     * @var ConfigurableFactory
     */
    private $configurableFactory;

    /**
     * Attribute Object.
     *
     * @var Attribute
     */
    private $attribute;

    /**
     * Cminds Builder for Configurable Products.
     *
     * @var ConfigurableBuilder
     */
    private $configurableBuilder;

    /**
     * Price Helper.
     *
     * @var Price
     */
    private $priceHelper;

    /**
     * Product Uploader Inventory Model.
     *
     * @var ProductUploaderInventory
     */
    private $productUploaderInventory;

    /**
     * Saveassociatedproduct constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param Transaction $transaction
     * @param CatalogProduct $catalogProduct
     * @param CmindsConfigurableProductModel $cmindsConfigurableModel
     * @param Configurable $configurable
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductFactory $productFactory
     * @param ConfigurableFactory $configurableFactory
     * @param Attribute $attribute
     * @param ConfigurableBuilder $configurableBuilder
     * @param Price $priceHelper
     * @param SourceRepositoryInterface $sourceRepository
     * @param ProductUploaderInventory $productUploaderInventory
     */
    public function __construct(
        Context $context,
        Data $helper,
        Transaction $transaction,
        CatalogProduct $catalogProduct,
        CmindsConfigurableProductModel $cmindsConfigurableModel,
        Configurable $configurable,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
        ConfigurableFactory $configurableFactory,
        Attribute $attribute,
        ConfigurableBuilder $configurableBuilder,
        Price $priceHelper,
        ProductUploaderInventory $productUploaderInventory
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->transaction = $transaction;
        $this->catalogProduct = $catalogProduct;
        $this->configurable = $configurable;
        $this->cmindsConfigurableModel = $cmindsConfigurableModel;
        $this->productFactory = $productFactory;
        $this->configurableFactory = $configurableFactory;
        $this->attribute = $attribute;
        $this->configurableBuilder = $configurableBuilder;
        $this->priceHelper = $priceHelper;
        $this->productUploaderInventory = $productUploaderInventory;
    }

    /**
     * Create Associated Product for Configurable Product.
     *
     * @return ResultInterface|void
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        if ($post = $this->getRequest()->getParams()) {
            try {
                $transaction = $this->transaction;
                $configurableProduct = $this->productFactory->create()
                    ->setStoreId(Store::DEFAULT_STORE_ID)
                    ->load($post['super_product_id']);

                if (!$this->canCreateAssociatedForProduct($configurableProduct)) {
                    throw new LocalizedException(__('Can not create associated product for configurable.'));
                }


                $transaction->addObject($configurableProduct);

                if (!isset($post['product_id']) || $post['product_id'] == 0) {
                    $product = $this->productFactory->create()
                        ->setStoreId(0)
                        ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                        ->setAttributeSetId(
                            $configurableProduct->getAttributeSetId()
                        );

                    $transaction->addObject($product);

                    $productStockDefaultExecute = true;

                    if (!$this->productUploaderInventory->inventoryIsSingleSourceMode()) {
                        $sources = [];
                        // all sources may be removed
                        if (isset($post['sources']) && is_array($post['sources'])) {
                            foreach ($post['sources'] as $sourceCode => $sourceValues) {
                                if ('default' === $sourceCode) {
                                    // fallback, if no qty set, the default source will be owerwritten with 0 value
                                    $post['qty'] = $sourceValues['inv'];
                                }

                                $sourceItem = [];
                                // Magento\InventoryApi\Api\SourceRepositoryInterface;
                                // check if such source is present
                                $source = $this->productUploaderInventory
                                        ->getSourceRepositoryObject()
                                        ->get($sourceCode);

                                if ($source) {
                                    //create the sourceItem using the factory
                                    $sourceItem['source_code'] = $sourceCode;
                                    $sourceItem['quantity'] = $sourceValues['inv'];
                                    $sourceItem['status'] = (bool)$sourceValues['status'];

                                    $sources[] = $sourceItem;
                                }
                            }
                        }

                        // skip setting qty to 0, this will lead to adding default source with 0 qty
                        // if (!count($sources)) {
                        $productStockDefaultExecute = false;
                        // }
                    }

                    if (true === $productStockDefaultExecute) {
                        $product->setStockData(
                            [
                                'is_in_stock' => ($post['qty'] > 0) ? 1 : 0,
                                'qty' => $post['qty'],
                            ]
                        );
                    }

                    $attributes = $product->getTypeInstance()
                        ->getEditableAttributes($configurableProduct);
                    foreach ($attributes as $attribute) {
                        if ($attribute->getIsUnique()
                            || $attribute->getAttributeCode() == 'url_key'
                            || $attribute->getFrontend()->getInputType() == 'gallery'
                            || $attribute->getFrontend()->getInputType() == 'media_image'
                            || !$attribute->getIsVisible()
                        ) {
                            continue;
                        }

                        $product->setData(
                            $attribute->getAttributeCode(),
                            $configurableProduct->getData(
                                $attribute->getAttributeCode()
                            )
                        );
                    }

                    $product->addData(
                        $this->getRequest()->getParams()
                    );
                    $product->setWebsiteIds(
                        $configurableProduct->getWebsiteIds()
                    );

                    $result['attributes'] = [];

                    $values = [];
                    if (isset($post['options'])) {
                        foreach ($post['options'] as $index => $option) {
                            $values[] = $post[$index];
                        }
                    }

                    foreach ($post as $name => $value) {
                        $product->setData($name, $value);
                    }

                    $sku = $configurableProduct->getSku();


                    $product
                        ->setName($post['name'])
                        ->setSku($sku)
                        ->setOptions([]) //this line is important. Without it the Product SaveHandler throws exception.
                        ->validate();

                    $this
                        ->getStoreManager()
                        ->setCurrentStore(Store::DEFAULT_STORE_ID);

                    $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);

                    if (true === $productStockDefaultExecute) {
                        $product->setStockData(
                            [
                                'is_in_stock' => ($post['qty'] > 0) ? 1 : 0,
                                'qty' => $post['qty'],
                            ]
                        );
                        $product->setQuantityAndStockStatus(
                            [
                                'qty' => $post['qty'],
                                'is_in_stock' => ($post['qty'] > 0) ? 1 : 0
                            ]
                        );
                    }

                    foreach ($post['options'] as $index => $opt) {
                        if (isset($opt['price'])) {
                            $price = $opt['price'];
                            $product->setPrice($this->priceHelper->convertToBaseCurrencyPrice($price));
                        }
                    }

                    $autoApprove = (bool)$this->scopeConfig
                        ->getValue(Saveconfigurable::PRODUCT_AUTO_APPROVAL);

                    if ($autoApprove) {
                        $product
                            ->setSupplierActivedProduct(1)
                            ->setFrontendproductProductStatus(CmindsModelProduct::STATUS_APPROVED)
                            ->setStockData(['is_in_stock' => 1]);
                    } elseif ($autoApprove === false) {
                        $product->setFrontendproductProductStatus(CmindsModelProduct::STATUS_PENDING);
                    }
                    $product->save();
                } else {
                    $product = $this->catalogProduct->load($post['product_id']);

                    if (!$product->getId()) {
                        throw new \Exception(__('Product doesn\'t not exists'));
                    }

                    if (!$this->helper->isOwner($product->getId())) {
                        throw new \Exception(__('Product doesn\'t belongs to you'));
                    }
                }


                $configurableModel = $this->cmindsConfigurableModel;
                $configurableModel->setProduct($configurableProduct);

                /** use builder to make new links for configurable products */
                $this->configurableBuilder->addNewLink($configurableProduct, $product);

                /** we need to execute it after the sku was generated and product was assigned */
                if (!$this->productUploaderInventory->inventoryIsSingleSourceMode()) {
                    $this->productUploaderInventory
                            ->getSourceItemsProcessorObject()
                            ->process((string)$product->getSku(), $sources);
                }

                $this->_redirect(
                    'supplier/product/associatedproducts',
                    ['id' => $post['super_product_id']]
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));

                $this->_redirect(
                    'supplier/product/associatedproducts',
                    ['id' => $post['super_product_id']]
                );
            }
        }
    }

    public function canCreateAssociatedForProduct(ProductInterface $entity)
    {
        if ($entity->getTypeId() !== 'configurable') {
            return false;
        }

        $params = $this->getRequest()->getParams();
        if (!$params || !isset($params['options'])) {
            return false;
        }

        $options = $params['options'];
        $configurableAttributes = $this->configurable->getConfigurableAttributes($entity);

        $exist = true;
        foreach ($configurableAttributes as $attribute) {
            foreach ($options as $code => $data) {
                if ($data['attribute_id'] === $attribute->getAttributeId()) {
                    if ($this->getRequest()->getParam($code) !== '') {
                        continue 2;
                    }
                }
            }

            $exist = false;
        }

        if (!$exist) {
            return false;
        }

        return true;
    }
}
