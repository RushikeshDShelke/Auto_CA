<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Attribute as AttributeHelper;
use Cminds\Supplierfrontendproductuploader\Model\Product as CmindsModelProduct;
use Cminds\Supplierfrontendproductuploader\Model\Product\Media\Video;
use Cminds\Supplierfrontendproductuploader\Model\Product\Builder as ProductBuilder;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\CatalogInventory\Api\StockRegistryInterface as StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Link as DownloadableLink;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Eav\Model\Entity\Attribute as AttributesCollection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Attribute\Management as AttributeManagement;

class Saveconfigurable extends AbstractController
{
    const IS_CONFIGURABLE = true;

    const PRODUCT_AUTO_APPROVAL = 'configuration/configure/products_auto_approval';

    const REQUIRE_PRODUCT_APPROVAL_AFTER_EDIT = 'configuration/configure/products_approval_reset_after_edit';

    /**
     * Product Entity.
     *
     * @var CatalogProduct
     */
    protected $catalogProduct;

    /**
     * Attributes Collection.
     *
     * @var AttributesCollection
     */
    protected $attributesCollection;

    /**
     * Scope Config Interface.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Cminds Product Object.
     *
     * @var CmindsModelProduct
     */
    protected $cmindsModelProduct;

    /**
     * Store Maganer
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Manager Interface.
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Donwloadable Link Object.
     *
     * @var DownloadableLink
     */
    protected $downloadableLink;

    /**
     * Stock Registry.
     *
     * @var StockRegistry
     */
    protected $stockRegistry;

    /**
     * Product Media Video Object.
     *
     * @var Video
     */
    protected $video;

    /**
     * Directory List.
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Product Builder.
     *
     * @var ProductBuilder
     */
    protected $builder;

    /**
     * @var AttributeManagement
     */
    private $attributeManagement;

    /**
     * Current store.
     *
     * @var Store|null
     */
    private $currentStore;
    
    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    private $priceHelper;

    /**
     * Saveconfigurable constructor.
     *
     * @param Context              $context
     * @param CatalogProduct       $product
     * @param AttributesCollection $attributesCollection
     * @param CmindsHelper          $helper
     * @param CmindsModelProduct    $cmindsProduct
     * @param DownloadableLink      $downloadable
     * @param StockRegistry         $stockRegistry
     * @param Video                 $video
     * @param DirectoryList         $directoryList
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     * @param ProductBuilder        $builder
     * @param AttributeManagement   $attributeManagement
     */
    public function __construct(
        Context $context,
        CatalogProduct $product,
        AttributesCollection $attributesCollection,
        CmindsHelper $helper,
        CmindsModelProduct $cmindsProduct,
        DownloadableLink $downloadable,
        StockRegistry $stockRegistry,
        Video $video,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ProductBuilder $builder,
        AttributeManagement $attributeManagement,
        AttributeHelper $attributeHelper,
        Price $priceHelper
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
        $this->cmindsModelProduct = $cmindsProduct;
        $this->downloadableLink = $downloadable;
        $this->stockRegistry = $stockRegistry;
        $this->video = $video;
        $this->directoryList = $directoryList;
        $this->builder = $builder;
        $this->attributeManagement = $attributeManagement;
        $this->attributeHelper = $attributeHelper;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Save Configurable Product.
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $this->currentStore = $this->getStoreManager()->getStore();

        $this
            ->getStoreManager()
            ->setCurrentStore(Store::DEFAULT_STORE_ID);

        if ($this->_request->getParams()) {            
            $autoApprove = (bool)$this->scopeConfig
                ->getValue(self::PRODUCT_AUTO_APPROVAL);
            $requireApproveAfterEdit = (bool)$this->scopeConfig
                ->getValue(self::REQUIRE_PRODUCT_APPROVAL_AFTER_EDIT);
            $postData = $this->_request->getParams();

            $editMode = false;
                // validate attribute set value                
                if( !in_array( $postData['attribute_set_id'] , $this->attributeHelper->getValidSetsIds() ) ){
                    $postData['attribute_set_id'] = $this->scopeConfig->getValue( 
                        $this->attributeHelper->getDefaultSetConfigKey() 
                    );
                }

            try {
                if (isset($postData['product_id'])
                    && $postData['product_id'] !== ''
                ) {
                    $product = $this->catalogProduct
                        ->setStoreId(Store::DEFAULT_STORE_ID)
                        ->load($postData['product_id']);

                    if (!$product->getId()) {
                        throw new \Exception('Product does not exists.');
                    }

                    $supplierId = $this
                        ->getHelper()
                        ->getSupplierId();
                    if ($product->getData('creator_id') !== $supplierId) {
                        throw new \Exception(
                            'Product does not belongs to this supplier.'
                        );
                    }

                    $editMode = true;
                } else {
                    $product = $this->catalogProduct->setStoreId(Store::DEFAULT_STORE_ID);
                }

                $hasBeenApproved = (int)$product->getFrontendproductProductStatus();

                /** just check if all base required attributes are filled (common attributes for any type) */
                $this->cmindsModelProduct
                    ->setData($postData)
                    ->validate(static::IS_CONFIGURABLE);

                $supplierId = $this
                    ->getHelper()
                    ->getSupplierId();

                $product->setData('creator_id', $supplierId);
                $product->setName($postData['name']);
                $product->setDescription($postData['description']);
                $product->setShortDescription($postData['short_description']);

                if (!$editMode) {
                    if (!isset($postData['sku']) || $postData['sku'] === null) {
                        $product->setSku(
                            $this
                                ->getHelper()
                                ->generateSku()
                        );
                    } else {
                        $cProduct = $this->catalogProduct->loadByAttribute(
                            'sku',
                            $postData['sku']
                        );

                        if ($cProduct) {
                            throw new \Exception(
                                'Product with this SKU already exists in catalog.'
                            );
                        }

                        $product->setSku($postData['sku']);
                    }
                    if (!isset($postData['attribute_set_id'])
                        || empty($postData['attribute_set_id'])
                    ) {
                        throw new \Exception('Missing Attribute Set ID.');
                    }

                    /** Here should be getParams('type') */
                    $typeId = $this
                        ->getRequest()
                        ->getParams();

                    if ($typeId['type'] === 'simple') {
                        $product->setTypeId(
                            Type::TYPE_SIMPLE
                        );
                    } elseif ($typeId['type'] === 'configurable') {
                        $product->setTypeId(
                            Configurable::TYPE_CODE
                        );
                    } elseif ($typeId['type'] === 'virtual') {
                        $product->setTypeId(
                            Type::TYPE_VIRTUAL
                        );
                    } elseif ($typeId['type'] === 'downloadable') {
                        $product->setTypeId(
                            DownloadableType::TYPE_DOWNLOADABLE
                        );
                    }

                    $product
                        ->setAttributeSetId($postData['attribute_set_id'])
                        ->setStatus(
                            Status::STATUS_ENABLED
                        )
                        ->setVisibility(
                            Visibility::VISIBILITY_NOT_VISIBLE
                        )
                        ->setTaxClassId(
                            $this->scopeConfig->getValue(
                                'products_settings/adding_products/product_tax_class'
                            )
                        )
                        ->setData('admin_product_note', null);
                }

                if (isset($postData['weight'])) {
                    $product->setWeight($postData['weight']);
                }

                if ($postData['price'] !== $product->getPrice()) {
                    $product->setPrice($this->priceHelper->convertToBaseCurrencyPrice($postData['price']));
                }

                $product
                    ->setCategoryIds($postData['category'])
                    ->setWebsiteIds(
                        [
                            $this->currentStore->getWebsiteId()
                        ]
                    );
                $product->setCreatedAt(strtotime('now'));

                if ($postData['special_price'] !== ''
                    && number_format($postData['special_price']) !== 0
                ) {
                    $product->setSpecialPrice($this->priceHelper->convertToBaseCurrencyPrice($postData['special_price']));

                    if ($postData['special_price_from_date'] !== null
                        && $postData['special_price_from_date'] !== ''
                    ) {
                        $product->setSpecialFromDate(
                            $postData['special_price_from_date']
                        );
                        $product->setSpecialFromDateIsFormated(true);
                    }
                    if ($postData['special_price_to_date'] !== null
                        && $postData['special_price_to_date'] !== ''
                    ) {
                        $product->setSpecialToDate(
                            $postData['special_price_to_date']
                        );
                        $product->setSpecialToDateIsFormated(true);
                    }
                }

                $this->getStoreManager()->setCurrentStore(Store::DEFAULT_STORE_ID);

                unset(
                    $postData['name'],
                    $postData['description'],
                    $postData['short_description'],
                    $postData['sku'],
                    $postData['weight'],
                    $postData['category']
                );

                $omitIndex = [
                    'submit',
                    'price',
                    'main_photo',
                    'image',
                    'product_id',
                    'special_price',
                    'special_price_to_date',
                    'special_price_from_date',
                    'notify_admin_about_change',
                ];
                if ($editMode) {
                    $omitIndex[] = 'attribute_set_id';
                }

                foreach ($postData as $index => $value) {
                    if (!in_array($index, $omitIndex) && $value !== '') {
                        $product->setData($index, $value);
                    }
                }

                if (!isset($postData['image'])) {
                    $postData['image'] = [];
                }

                $existingImages = [];
                if ($product->getId() && $editMode) {
                    $objectManager = $this->_objectManager;

                    /** Remove Images From Product */
                    $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

                    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                    foreach ($existingMediaGalleryEntries as $key => $entry) {
                        $photo_data = $entry->getData();
                        $inArray = in_array(
                            $photo_data['file'],
                            $postData['image']
                        );

                        if (!$inArray) {
                            unset($existingMediaGalleryEntries[$key]);
                        } else {
                            $existingImages[] = $photo_data['file'];
                        }
                    }

                    $product->setMediaGalleryEntries($existingMediaGalleryEntries);

                    if (!empty($existingMediaGalleryEntries)) {
                        $productRepository->save($product);
                    }
                }
                $onlyOneImage = false;

                if (count($postData['image']) === 1) {
                    $onlyOneImage = true;
                }

                $addedImages = [];
                foreach ($postData['image'] as $image) {
                    if ($image !== '' && $image && $image !== null
                        && !in_array($image, $existingImages)
                    ) {
                        $attrs = null;

                        $addedImages[] = $image;

                        if ($image === $postData['main_photo'] || $onlyOneImage) {
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
                                $this->getHelper()->getImageCacheDir($postData) . "/resized" . $image,
                                $attrs,
                                false,
                                false
                            );
                        }
                    }
                }

                $totalUploadedImagesCount
                    = count($addedImages) + count($existingImages);

                $value = $this->scopeConfig->getValue(
                    'products_settings/adding_products/maximum_allowed_images'
                );
                if ($totalUploadedImagesCount > $value) {
                    throw new \Exception(
                        'You can\'t upload that amount of images. Limit: ' . $value
                    );
                }

                if (!empty($postData['video_url'])) {
                    if (strpos($postData['video_url'], 'youtube') === false) {
                        throw new \Exception(
                            'We support only youtube videos.'
                        );
                    }
                    $this->video->setVideo(
                        $product,
                        $postData['video_url'],
                        $this->directoryList->getPath('media')
                    );
                }

                if ($editMode) {
                    if (isset($postData['main_photo'])) {
                        $product->setSmallImage($postData['main_photo']);
                        $product->setImage($postData['main_photo']);
                        $product->setThumbnail($postData['main_photo']);
                    }
                } else {
                    $product->setData(
                        'creator_id',
                        $this->getHelper()->getSupplierId()
                    );
                }

                if ((isset($postData['attributes']) === false || $postData['attributes'] === false)
                    && $editMode === false
                ) {
                    throw new LocalizedException(__('Configuration attributes are not selected.'));
                }

                /** Add configurable attributes to the product here */
                if ($editMode === false && isset($postData['attributes'])) {
                    $product = $this->builder
                        ->fillProductWithConfigurableAttributes($product, $postData['attributes']);
                } elseif ($editMode === true) {
                    $assignedAttributes = $product->getTypeInstance()->getConfigurableOptions($product);
                    $configAttributeSet = $product->getAttributeSetId();
                    $attributes = $this->attributeManagement->getAttributes($configAttributeSet);
                    $attributesToSave = [];

                    foreach ($assignedAttributes as $attributeId => $value) {
                        $attributesToSave[] = $attributes[$attributeId]->getAttributeCode();
                    }

                    $product = $this->builder
                        ->fillProductWithConfigurableAttributes($product, $attributesToSave);
                }

                if ($autoApprove) {
                    $product
                        ->setSupplierActivedProduct(1)
                        ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
                        ->setFrontendproductProductStatus(CmindsModelProduct::STATUS_APPROVED)
                        ->setStockData(['is_in_stock' => 1]);
                }

                if ($autoApprove === false && $requireApproveAfterEdit === true && $editMode === true) {
                    $product->setFrontendproductProductStatus(CmindsModelProduct::STATUS_PENDING);
                }

                if ($editMode === true
                    && $autoApprove === false
                    && $requireApproveAfterEdit === false
                    && $hasBeenApproved === CmindsModelProduct::STATUS_APPROVED
                ) {
                    $product
                        ->setSupplierActivedProduct(1)
                        ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
                        ->setFrontendproductProductStatus(CmindsModelProduct::STATUS_APPROVED)
                        ->setStockData(['is_in_stock' => 1]);
                }

                $product->save();

                $this->_redirect('supplier/product/productlist');
            } catch (\Exception $ex) {
                $this->messageManager->addError($ex->getMessage());

                if ($editMode) {
                    $this->_redirect(
                        'supplier/product/editconfigurable/',
                        [
                            'id' => $postData['product_id'],
                            'type' => $postData['type'],
                        ]
                    );
                } else {
                    $this->_redirect(
                        'supplier/product/createconfigurable',
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
