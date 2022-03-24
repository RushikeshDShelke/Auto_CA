<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Attribute as AttributeHelper;
use Cminds\Supplierfrontendproductuploader\Model\Product as CmindsModelProduct;
use Cminds\Supplierfrontendproductuploader\Model\Product\Media\Video;
use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable as CmindsDownloadableProduct;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface as StockRegistry;
use Magento\Downloadable\Model\Link as DownloadableLink;
use Magento\Eav\Model\Entity\Attribute as AttributesCollection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Helper\Download;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\App\ProductMetadataInterface;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class Save extends AbstractController
{
    protected $catalogProduct;
    protected $productFactory;
    protected $attributesCollection;
    protected $cmindsModelProduct;
    protected $productRepository;
    protected $downloadableLink;
    protected $stockRegistry;
    protected $editMode = false;
    protected $post = [];
    protected $video;
    protected $resourceConnection = [];
    protected $directoryList;
    protected $cmindsDownloadableProduct;
    protected $linkRepository;
    protected $productAction;
    protected $productMetadataInterface;
    protected $currentStore;
    protected $priceHelper;

    /**
     * SourceRepositoryInterface
     */
    protected $productUploaderInventory;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    public function __construct(
        Context $context,
        CatalogProduct $product,
        AttributesCollection $attributesCollection,
        CmindsHelper $helper,
        CmindsModelProduct $cmindsProduct,
        DownloadableLink $downloadable,
        StockRegistry $stockRegistry,
        ResourceConnection $resourceConnection,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Video $video,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CmindsDownloadableProduct $cmindsDownloadableProduct,
        LinkRepository $linkRepository,
        ProductAction $productAction,
        ProductMetadataInterface $productMetadataInterface,
        AttributeHelper $attributeHelper,
        Price $priceHelper,
        ProductUploaderInventory $productUploaderInventory
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->messageManager = $context->getMessageManager();
        $this->catalogProduct = $product;
        $this->attributesCollection = $attributesCollection;
        $this->scopeConfig = $scopeConfig;
        $this->cmindsModelProduct = $cmindsProduct;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->downloadableLink = $downloadable;
        $this->resourceConnection = $resourceConnection;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->video = $video;
        $this->directoryList = $directoryList;
        $this->cmindsDownloadableProduct = $cmindsDownloadableProduct;
        $this->linkRepository = $linkRepository;
        $this->productAction = $productAction;
        $this->productMetadataInterface = $productMetadataInterface;
        $this->attributeHelper = $attributeHelper;
        $this->priceHelper = $priceHelper;

        $this->productUploaderInventory = $productUploaderInventory;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $this->currentStore = $this->getStoreManager()->getStore();

        $this->getStoreManager()
            ->setCurrentStore(Store::DEFAULT_STORE_ID);

        if ($this->getRequest()->getParams()) {
            $autoApprove = $this->scopeConfig
                ->getValue('configuration/configure/products_auto_approval');
            $postData = $this->getRequest()->getParams();

            try {
                // validate attribute set value
                if (!in_array($postData['attribute_set_id'], $this->attributeHelper->getValidSetsIds())) {
                        $postData['attribute_set_id'] = $this->scopeConfig->getValue(
                            $this->attributeHelper->getDefaultSetConfigKey()
                        );
                }
                if ($postData['marketplace_fee']) {
                    $postData['price'] = $postData['marketplace_fee'];
                }

                $this->post = $postData;
                $product = $this->prepare();

                $this->cmindsModelProduct->setData($postData);
                $this->cmindsModelProduct->validate();

                $this->setDefaults($product);
                $this->setConfigurableAttributes($product);

                unset(
                    $postData['name'],
                    $postData['description'],
                    $postData['short_description'],
                    $postData['sku'],
                    $postData['weight'],
                    $postData['price'],
                    $postData['category']
                );

                $omitIndex = [
                    'submit',
                    'main_photo',
                    'image',
                    'product_id',
                    'special_price',
                    'special_price_to_date',
                    'special_price_from_date',
                    'notify_admin_about_change',
                ];
                if ($this->editMode) {
                    $omitIndex[] = 'attribute_set_id';
                }

                foreach ($postData as $index => $value) {
                    if (!in_array($index, $omitIndex, true) && !empty($value)) {
                        $product->setData($index, $value);
                    }
                }

                // $product->save();

                $productStockStatus = 1;
                $productStockDefaultExecute = true;

                if (!$this->productUploaderInventory->inventoryIsSingleSourceMode()) {
                    $sources = [];
                    // all sources may be removed
                    if (isset($postData['sources']) && is_array($postData['sources'])) {
                        foreach ($postData['sources'] as $sourceCode => $sourceValues) {
                            if ('default' === $sourceCode) {
                                // fallback, if no qty set, the default source will be owerwritten with 0 value
                                $postData['qty'] = $sourceValues['inv'];
                                $productStockStatus = $sourceValues['status'];
                            }

                            $sourceItem = [];
                            // Magento\InventoryApi\Api\SourceRepositoryInterface;
                            // check if such source is present
                            $source = $this->productUploaderInventory->getSourceRepositoryObject()->get($sourceCode);

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
                    if (!count($sources)) {
                        $productStockDefaultExecute = false;
                    }

                    $this->productUploaderInventory->getSourceItemsProcessorObject()->process((string)$product->getSku(), $sources);
                }

                if (true === $productStockDefaultExecute) {
                    $qty = 0;
                    if (isset($postData['qty'])) {
                        $qty = $postData['qty'];
                    }

                    /** Save quantity and stock data here */
                    $product = $this->setProductQuantity($product, $qty);

                    $product->setStockData(['qty' => $qty, 'is_in_stock' => $productStockStatus]);
                    $product->setQuantityAndStockStatus(['qty' => $qty, 'is_in_stock' => $productStockStatus]);
                }
                $product->save();



                $product = $this->productFactory->create()
                    ->setStoreId(Store::DEFAULT_STORE_ID)
                    ->load($product->getId());

                /** I think it doesn't make sense because there is the same code in the setDefaults method */
                if (!$product->getSpecialPrice()) {
                    $product->setSpecialPrice(null);
                    $product->setSpecialFromDate(null);
                    $product->setSpecialToDate(null);
                }

                /**
                 * Clear product repository cache because product was already cached in save method.
                 * Needed in order to save downloadable links while editing the product
                 *
                 * if version is lower than 2.1.0, then make force reload of the product. If equal or higher,
                 * then just clean the cache
                 */
                if (version_compare($this->productMetadataInterface->getVersion(), '2.1.0') === -1) {
                    $this->productRepository->get(
                        $product->getSku(),
                        false,
                        Store::DEFAULT_STORE_ID,
                        true
                    );
                } else {
                    $this->productRepository->cleanCache();
                }

                if (isset($postData['is_cloned'])
                    && $postData['is_cloned']
                    && $postData['type'] === 'downloadable'
                ) {
                    $files = $this->getRequest()->getFiles();
                    $links = $this->getRequest()->getParam('downloadable_links', []);

                    $this->cmindsDownloadableProduct->saveLinks(
                        $product->getId(),
                        $files,
                        Store::DEFAULT_STORE_ID,
                        $links
                    );

                    $product->setHasOptions(true);
                } elseif ($postData['type'] === Downloadable::TYPE_DOWNLOADABLE) {
                    $files = $this->getRequest()->getFiles();

                    if ($this->editMode === true) {
                        $links = $this->getRequest()->getParam('downloadable_links', []);

                        $this->cmindsDownloadableProduct->saveLinks(
                            $product->getId(),
                            $files,
                            Store::DEFAULT_STORE_ID,
                            $links
                        );
                    } else {
                        $data = [];

                        if (isset($postData['link_title']) && $postData['link_title'] !== '') {
                            $data['title'] = $postData['link_title'] ?: '';
                        }

                        if (isset($files['downloadable_upload']['name']) && $files['downloadable_upload']['name'] !== '') {
                            $this->cmindsDownloadableProduct->createLinks(
                                $product->getId(),
                                $files,
                                Store::DEFAULT_STORE_ID,
                                $data
                            );
                        } elseif (!empty($postData['file_url'])) {
                            $data['link_url'] = $postData['file_url'];
                            $this->cmindsDownloadableProduct->createLinks(
                                $product->getId(),
                                null,
                                Store::DEFAULT_STORE_ID,
                                $data
                            );
                        }
                    }

                    $product->setHasOptions(true);
                }

                $productLinks = $this->linkRepository->getList($product->getSku());
                $product
                    ->getExtensionAttributes()
                    ->setDownloadableProductLinks($productLinks);

                if (!isset($postData['image'])) {
                    $postData['image'] = [];
                }

                /** This array contains not only images but videos as well */
                $existingImages = [];
                $countExistingImages = 0;

                if ($product->getId() && $this->editMode) {
                    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();

                    foreach ($existingMediaGalleryEntries as $key => $entry) {
                        $photo_data = $entry->getData();
                        $inArray = in_array($photo_data['file'], $postData['image']);
                        if (!$inArray) {
                            unset($existingMediaGalleryEntries[$key]);
                        } else {
                            if ($photo_data['media_type'] === 'image') {
                                $countExistingImages++;
                            }

                            $existingImages[] = $photo_data['file'];
                        }
                    }

                    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                    $this->productRepository->save($product);
                }

                $onlyOneImage = false;

                if (count($postData['image']) === 1) {
                    $onlyOneImage = true;
                }

                $maximumAllowedImages = $this->scopeConfig->getValue(
                    'products_settings/adding_products/maximum_allowed_images'
                );
                $addedImages = [];

                foreach ($postData['image'] as $image) {
                    if ($image != '' && $image
                        && $image != null
                        && !in_array($image, $existingImages)
                    ) {
                        $attrs = null;

                        $addedImages[] = $image;

                        if (count($addedImages) + $countExistingImages > $maximumAllowedImages) {
                            $this->messageManager->addErrorMessage(
                                __("You can't upload that amount of images. Limit: %1", $maximumAllowedImages)
                            );
                            break;
                        }

                        if ($image == $postData['main_photo'] || $onlyOneImage) {
                            $attrs = ['image', 'small_image', 'thumbnail'];
                        }

                        if (isset($postData['is_cloned'])) {
                            $product->addImageToMediaGallery(
                                'catalog/product' . $image,
                                $attrs,
                                true,
                                false
                            );
                        } else {
                            $product->addImageToMediaGallery(
                                $this->getHelper()->getImageCacheDir($postData)
                                . $image,
                                $attrs,
                                false,
                                false
                            );
                        }
                    }
                }


                if (!empty($postData['video_url'])) {
                    if (strpos($postData['video_url'], 'youtube') === false) {
                        throw new IntegrationException(
                            __('We support only youtube videos.')
                        );
                    }
                    $this->video->setVideo(
                        $product,
                        $postData['video_url'],
                        $this->directoryList->getPath('media')
                    );
                }

                $product->save();

                /** Update main images when editing product */
                if (isset($postData['main_photo']) &&
                    $postData['main_photo'] !== null
                ) {
                    $mainPhoto = $postData['main_photo'];

                    /**
                     *  The if snippet is used when product is created for first time ot copied.
                     *  The else runs when product is run in edit mode.
                     */
                    if ($this->editMode !== true) {
                        $this->productAction
                            ->updateAttributes(
                                [$product->getId()],
                                [
                                    'image' => $mainPhoto,
                                    'small_image' => $mainPhoto,
                                    'thumbnail' => $mainPhoto
                                ],
                                Store::DEFAULT_STORE_ID
                            );
                    } else {
                        $product
                            ->setImage($mainPhoto)
                            ->setSmallImage($mainPhoto)
                            ->setThumbnail($mainPhoto)
                            ->save();
                    }
                }

                if ($this->editMode === false) {
                    $product
                        ->setData(
                            'creator_id',
                            $this->helper->getSupplierId()
                        );

                    if ($autoApprove) {
                        $product
                            ->setSupplierActivedProduct(1)
                            ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
                            ->setFrontendproductProductStatus(CmindsModelProduct::STATUS_APPROVED);
                    } else {
                        $product
                            ->setData(
                                'frontendproduct_product_status',
                                \Cminds\Supplierfrontendproductuploader\Model\Product::STATUS_PENDING
                            );
                    }
                }

                if ($autoApprove) {
                    $product
                        ->setSupplierActivedProduct(1)
                        ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
                        ->setFrontendproductProductStatus(CmindsModelProduct::STATUS_APPROVED);
                }

                if ($postData['type'] === 'downloadable') {
                    $product->unsetData('downloadable_data');
                }

                $product->save();

                // uncomment after grouped product clone template fix
                if ($postData['type'] === 'grouped') {
                    $stockItem = $this->stockRegistry
                        ->getStockItem($product->getId());
                    $stockItem
                        ->setIsInStock($postData['stock'] ? true : false)
                        ->save();

                    $this->setGroupedProducts(
                        $product,
                        $postData['group_products_ids'],
                        $postData['group_products_qty']
                    );
                }

                $this->_redirect('supplier/product/productlist');
            } catch (\Exception $ex) {
                $this->messageManager->addError($ex->getMessage());

                if ($this->editMode) {
                    if ($postData['type'] === 'grouped') {
                        $this->_redirect(
                            'supplier/product/editgrouped/',
                            [
                                'id' => $postData['product_id'],
                                'type' => $postData['type'],
                            ]
                        );
                    } else {
                        $this->_redirect(
                            'supplier/product/edit/',
                            [
                                'id' => $postData['product_id'],
                                'type' => $postData['type'],
                            ]
                        );
                    }
                } else {
                    if ($postData['type'] === 'grouped') {
                        $this->_redirect(
                            'supplier/product/creategrouped',
                            [
                                //'attribute_set_id' => $postData['attribute_set_id'],
                                'type' => $postData['type'],
                            ]
                        );
                    } else {
                        $this->_redirect(
                            'supplier/product/create',
                            [
                                'attribute_set_id' => $postData['attribute_set_id'],
                                'type' => $postData['type'],
                            ]
                        );
                    }
                }
            }
        }
    }

    protected function setGroupedProducts($product, $grouped_ids, $grouped_qty)
    {
        if ($this->editMode) {
            $this->resourceConnection->getConnection('core_write')->query(
                "delete from catalog_product_link where product_id ="
                . $product->getId()
            );
        }

        $i = 0;
        foreach ($grouped_ids as $id) {
            if ($id !== '') {
                $this->resourceConnection->getConnection('core_write')->query(
                    "insert into catalog_product_link (linked_product_id, "
                    . "product_id,link_type_id) values (" . $id . ","
                    . $product->getId() . ",3)"
                );
                $link_id = $this->resourceConnection->getConnection('core_write')->lastInsertId();
                $this->resourceConnection->getConnection('core_write')->query(
                    "insert into catalog_product_link_attribute_decimal "
                    . "(product_link_attribute_id, link_id,value) values (5,"
                    . $link_id . "," . $grouped_qty[$i] . ")"
                );
                $i++;
            }
        }
    }

    /**
     * @return Product
     * @throws LocalizedException
     */
    protected function prepare()
    {
        if (isset($this->post['product_id'])) {
            $product = $this->productFactory
                ->create()
                ->setStoreId(Store::DEFAULT_STORE_ID)
                ->load($this->post['product_id']);

            if (!$product->getId()) {
                throw new LocalizedException(__('Product which you are trying to edit does not exists.'));
            }

            $productSupplierId = (int)$product->getData('creator_id');
            $currentSupplierId = (int)$this->helper->getSupplierId();

            if ($productSupplierId !== $currentSupplierId) {
                throw new LocalizedException(__('You can not edit product which does not belongs to you.'));
            }

            $this->editMode = true;
        } else {
            $product = $this->productFactory->create()
                ->setStoreId(Store::DEFAULT_STORE_ID);
        }

        return $product;
    }

    protected function setDefaults($product)
    {
        $product->setName($this->post['name']);
        $product->setDescription($this->post['description']);
        // $product->setShortDescription($this->post['short_description']);

        if (!$this->editMode) {
            if (!isset($this->post['sku'])) {
                $product->setSku($this->helper->generateSku());
            } else {
                $cProduct = $this->catalogProduct->loadByAttribute(
                    'sku',
                    $this->post['sku']
                );

                if ($cProduct) {
                    throw new LocalizedException(
                        __('Product with this SKU already exists in catalog.')
                    );
                }

                $product->setSku($this->post['sku']);
            }

            $type = $this->getRequest()->getParam('type');
            if ($type === 'simple' or in_array($type, $this->helper->getAvailableTypes())) {
                $product->setTypeId($type);
            } else {
                throw new LocalizedException(__('Unsupported product type selected.'));
            }

            if (!isset($this->post['attribute_set_id'])
                || empty($this->post['attribute_set_id'])
            ) {
                throw new LocalizedException(__('Missing attribute set ID.'));
            }

            $product->setAttributeSetId($this->post['attribute_set_id']);

            $product->setStatus(
                Status::STATUS_ENABLED
            );
            $product->setVisibility(
                Visibility::VISIBILITY_NOT_VISIBLE
            );

            $product->setTaxClassId(
                $this->scopeConfig->getValue(
                    'products_settings/adding_products/product_tax_class'
                )
            );
            $product->setData('admin_product_note', null);
        }

        if (isset($this->post['weight'])) {
            $product->setWeight($this->post['weight']);
        }

        if (isset($this->post['price'])) {
            $product->setPrice($this->convertToBaseCurrencyPrice($this->post['price']));
        }

        $product->setCategoryIds($this->post['category']);
        $product->setWebsiteIds(
            [
                $this->currentStore->getWebsiteId(),
            ]
        );
        $product->setCreatedAt(strtotime('now'));

        $product
            ->setSpecialPrice(null)
            ->setSpecialFromDate(null)
            ->setSpecialToDate(null);

        if (!empty($this->post['special_price'])
            && number_format($this->post['special_price']) !== 0
        ) {
            $product->setSpecialPrice($this->convertToBaseCurrencyPrice($this->post['special_price']));

            if (!empty($this->post['special_price_from_date'])) {
                $product->setSpecialFromDate(
                    $this->post['special_price_from_date']
                );
                $product->setSpecialFromDateIsFormated(true);
            }
            if (!empty($this->post['special_price_to_date'])) {
                $product->setSpecialToDate(
                    $this->post['special_price_to_date']
                );
                $product->setSpecialToDateIsFormated(true);
            }
        }
    }

    protected function convertToBaseCurrencyPrice($price)
    {
        if (!$price) {
            return 0;
        }

        $amount = $this->priceHelper->convertToBaseCurrencyPrice($price);

        return $amount;
    }

    protected function setConfigurableAttributes($product)
    {
        if (isset($this->post['attributes'])) {
            foreach ($this->post['attributes'] as $attrCode) {
                $super_attribute = $this->attributesCollection
                    ->loadByCode(
                        'catalog_product',
                        $attrCode
                    );

                $configurableAtt = $this->configurableAttributeFactory
                    ->create()
                    ->setProductAttribute($super_attribute);

                $newAttributes[] = [
                    'id' => $configurableAtt->getId(),
                    'label' => $configurableAtt->getLabel(),
                    'position' => $super_attribute->getPosition(),
                    'values' => $configurableAtt->getPrices()
                        ? $product->getPrices()
                        : [],
                    'attribute_id' => $super_attribute->getId(),
                    'attribute_code' => $super_attribute->getAttributeCode(),
                    'frontend_label' => $super_attribute->getFrontend()->getLabel(),
                ];
            }
        }
    }

    protected function prepareFiles($product, $ret)
    {
        if ($ret !== null) {
            $downloadData = [];
            if ($this->editMode) {
                $link = $this->downloadableLink
                    ->load($product->getId(), 'product_id');
            } else {
                $link = new \Magento\Framework\DataObject();
            }

            $product->setLinksTitle('Test Product');
            $product->setLinksPurchasedSeparately('0');

            $downloadData['link'][] = [
                'link_id' => $link->getId(),
                'product_id' => $product->getId(),
                'website_id' => 0,
                'title' => $ret['name'],
                'price' => $product->getPrice(),
                'number_of_downloads' => (int)99999,
                'is_shareable' => Link::LINK_SHAREABLE_CONFIG,
                'type' => Download::LINK_TYPE_FILE,
                'file' => json_encode(
                    [
                        [
                            'file' => $ret['url'],
                            'name' => $ret['name'],
                            'size' => $ret['size'],
                            'status' => 0,
                        ],
                    ]
                ),
                'link_url' => null,
                'sort_order' => 0,
                'is_delete' => 0,
            ];

            $product->setDownloadableData($downloadData);
        } elseif (isset($this->post['file_url']) && $this->post['file_url']) {
            if (!$this->editMode) {
                $downloadData = [];

                $downloadData['link'][0] = [
                    'link_id' => '',
                    'title' => $this->post['file_url'],
                    'price' => $product->getPrice(),
                    'number_of_downloads' => (int)9999,
                    'is_shareable' => Link::LINK_SHAREABLE_CONFIG,
                    'file' => $product->getLinkFile(),
                    'type' => 'url',
                    'link_url' => $this->post['file_url'],
                    'sort_order' => 0,
                    'is_delete' => 0,
                ];

                $product->setDownloadableData($downloadData);
            } else {
                $linkPurchasedItems = $this->downloadableLink->getCollection()
                    ->addFieldToFilter('product_id', $product->getId())->load();
                $currentPurchasedItemsT = $linkPurchasedItems->getItems();

                foreach ($currentPurchasedItemsT as $c) {
                    $c->setLinkUrl($this->post['file_url']);
                    $c->save();
                }
            }
        }
    }

    protected function setProductQuantity(Product $product, $qty)
    {
        if (!$qty) {
            $qty = 0;
        }

        if ($product->getId()) {
            $stockItem = $this->stockRegistry
                ->getStockItem($product->getId());
            $stockItem
                ->setQty($qty)
                ->setIsInStock($qty ? true : false)
                ->save();
        }

        return $product->setQuantityAndStockStatus(['qty' => $qty, 'is_in_stock' => $qty ? 1:0]);
    }
}
