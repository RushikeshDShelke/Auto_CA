<?php

namespace Cminds\Ordercomment\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class Data extends AbstractHelper
{
    protected $customerSession;
    protected $productFactory;
    protected $resourceConfig;
    protected $directoryList;
    protected $customerFactory;
    protected $customerUrl;
    protected $storeManager;
    protected $urlRewrite;
    protected $urlBuilder;
    protected $productCollectionFactory;
    protected $productStatus;
    protected $productVisibility;
    protected $session;
    protected $_productRepository;
    
    /**
     * @var ModuleManager
     */
    protected $moduleManager;


    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProductFactory $productFactory
     * @param ResourceConfig $resourceConfig
     * @param CustomerFactory $customerFactory
     * @param DirectoryList $directoryList
     * @param CustomerUrl $customerUrl
     * @param StoreManagerInterface $storeManager
     * @param UrlRewrite $urlRewrite
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductStatus $productStatus
     * @param Session $session
     * @param Visibility $productVisibility
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductFactory $productFactory,
        ResourceConfig $resourceConfig,
        CustomerFactory $customerFactory,
        DirectoryList $directoryList,
        CustomerUrl $customerUrl,
        StoreManagerInterface $storeManager,
        UrlRewrite $urlRewrite,
        ProductCollectionFactory $productCollectionFactory,
        ProductStatus $productStatus,
        Session $session,
        Visibility $productVisibility,
        ProductMetadataInterface $productMetadata,
        ModuleManager $moduleManager,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->resourceConfig = $resourceConfig;
        $this->directoryList = $directoryList;
        $this->customerFactory = $customerFactory;
        $this->customerUrl = $customerUrl;
        $this->storeManager = $storeManager;
        $this->urlRewrite = $urlRewrite;
        $this->session = $session;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
        $this->_productRepository = $productRepository;
    }


    public function isEnabled()
    {
        $value = (int)$this->scopeConfig->getValue(
            'configuration/configure/module_enabled'
        );

        return $value === 1;
    }

    public function getLoggedSupplier()
    {
        return $this->customerSession->getCustomer();
    }

    public function getSupplierLoginPage()
    {
        $value = (int)$this->scopeConfig->getValue(
            'configuration/registration_and_login/separated_login_page'
        );

        $useSeparated = $value === 1;

        if ($useSeparated) {
            return $this->_getUrl('supplier/account/login');
        }

        return $this->customerUrl->getLoginUrl();
    }

    public function canRegister()
    {
        $value = (int)$this->scopeConfig->getValue(
            'configuration/registration_and_login/allow_suppliers_register'
        );

        return $value === 1;
    }

    public function canLogin()
    {
        $value = (int)$this->scopeConfig->getValue(
            'configuration/registration_and_login/separated_login_page'
        );

        return $value === 1;
    }

    public function noAccessInformation()
    {
        if ($this->customerSession->isLoggedIn()) {
            return !$this->hasAccess();
        }
    }

    public function isSupplier($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);

        $groupId = (int)$customer->getGroupId();
        $allowedGroups = $this->getAllowedGroups();

        return in_array($groupId, $allowedGroups, true);
    }

    public function isSupplierNeedsToBeApproved()
    {
        return $this->scopeConfig->getValue(
            'configuration/configure/supplier_needs_to_be_approved'
        );
    }

    public function hasAccess()
    {
        return $this->canAccess();
    }

    public function canEditProducts()
    {
        $editorGroupConfig = (int)$this->scopeConfig->getValue(
            'configuration/'
            . 'suppliers_group/'
            . 'suppliert_group_which_can_edit_own_products'
        );

        $id = (int)$this->customerSession->getId();
        $customer = $this->customerFactory->create()->load($id);
        $groupId = $customer->getGroupId();


        $allowedGroups = [];

        if ($editorGroupConfig !== null) {
            $allowedGroups[] = $editorGroupConfig;
        } else {
            return true;
        }

        return in_array($groupId, $allowedGroups);
    }

    public function getAllowedGroups()
    {
        $editorGroupConfig = $this->scopeConfig->getValue(
            'configuration/suppliers_group/supplier_group'
        );
        $regularGroupConfig = $this->scopeConfig->getValue(
            'configuration/'
            . 'suppliers_group/'
            . 'suppliert_group_which_can_edit_own_products'
        );

        $allowedGroups = [];

        if ($editorGroupConfig !== null) {
            $allowedGroups[] = (int)$editorGroupConfig;
        }

        if ($editorGroupConfig !== $regularGroupConfig) {
            $allowedGroups[] = (int)$regularGroupConfig;
        }

        return $allowedGroups;
    }

    public function getSupplierId()
    {
        if ($this->hasAccess()) {
            $customer = $this->customerSession->getCustomer();

            return $customer->getId();
        }

        return false;
    }

    public function generateSku()
    {
        $sku = $this->scopeConfig->getValue(
            'products_settings/adding_products/auto_increment_sku_number'
        );

        while (true) {
            $product = $this->productFactory->create()
                ->getCollection()
                ->addFieldToFilter('sku', $sku)
                ->setPageSize(1)
                ->setCurPage(1);

            if (!$product->getSize()) {
                break;
            }

            $sku++;
        }

        $coreConfig = $this->resourceConfig;
        $coreConfig->saveConfig(
            'supplierfrontendproductuploader_products/'
            . 'supplierfrontendproductuploader_catalog_config/'
            . 'sku_schema',
            $sku,
            'default',
            0
        );

        return $sku;
    }

    public function getImageCacheDir()
    {
        return $this->directoryList->getPath(DirectoryList::UPLOAD);
    }

    public function getImageDir()
    {
        $path = $this->directoryList->getPath(
            './pub/'
            . DirectoryList::MEDIA
            . '/catalog/product'
        );

        return $path;
    }

    public function getProductSupplierId($_product)
    {
        $product = $this->getProductBySku($_product['sku']);
        return $product->getCreatorId();
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    public function canAccess($isSecured = false)
    {
        if (!$this->customerSession && !$isSecured) {
            return false;
        }

        $customerGroupId = (int)$this->customerSession->getCustomer()->getGroupId();
        $allowedGroupIds = $this->getAllowedGroups();

        if (in_array($customerGroupId, $allowedGroupIds, true)) {
            return true;
        }

        return false;
    }

    public function getAvailableTypes()
    {
        $types = $this->scopeConfig->getValue(
            'products_settings/adding_products/allowed_product_types'
        );

        return explode(',', strtolower($types));
    }

    public function canCreateVirtualProduct()
    {
        $types = $this->scopeConfig->getValue(
            'products_settings/adding_products/allowed_product_types'
        );

        return (int)in_array('VIRTUAL', explode(',', $types), true);
    }

    public function canCreateGroupedProduct()
    {
        $types = $this->scopeConfig->getValue(
            'products_settings/adding_products/allowed_product_types'
        );

        return (int)in_array('GROUPED', explode(',', $types), true);
    }

    public function canCreateDownloadableProduct()
    {
        $types = $this->scopeConfig->getValue(
            'products_settings/adding_products/allowed_product_types'
        );

        return (int)in_array('DOWNLOADABLE', explode(',', $types), true);
    }

    public function canCreateConfigurableProduct()
    {
        $types = $this->scopeConfig->getValue(
            'products_settings/adding_products/allowed_product_types'
        );

        return (int)in_array('CONFIGURABLE', explode(',', $types), true);
    }

    public function getAvailableExtensions()
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                'products_settings/downloadable_product_settings/extension_type'
            )
        );
    }

    public function isOwner($product, $supplierId = false)
    {
        if (!$supplierId) {
            $supplierId = $this->getSupplierId();
        }

        $ownerId = $this->getSupplierIdByProductId($product);

        return $supplierId === $ownerId;
    }

    public function getSupplierIdByProductId($productId)
    {
        $product = $this->productFactory->create()->load($productId);
        $supplierId = $product->getCreatorId();

        return $supplierId;
    }

    public function getMaxImages()
    {
        $imagesCount = $this->scopeConfig->getValue(
            'products_settings/adding_products/maximum_allowed_images'
        );

        if ($imagesCount === null || $imagesCount === '') {
            $imagesCount = 0;
        }

        $maxProducts = $this->scopeConfig->getValue(
            'configuration/csv_import/how_many_product_can_be_imported'
        );

        if ($maxProducts > 0) {
            $imagesCount *= $maxProducts;
        } else {
            $imagesCount = 999999999999999999;
        }

        return $imagesCount;
    }

    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    /**
     * Method generate csv file from array.
     *
     * @param array $array
     *
     * @return null|string
     */
    public function array2Csv(array $array)
    {
        if (count($array) === 0) {
            return null;
        }

        ob_start();
        $df = fopen('php://output', 'wb');
        fputcsv($df, array_values($array));

        return ob_get_clean();
    }

    public function prepareCsvHeaders($filename = 'sample_import_file.csv')
    {
        header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
        header(
            'Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate'
        );

        $now = gmdate('D, d M Y H:i:s');
        header('Last-Modified: ' . $now . ' GMT');

        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');

        header('Content-Disposition: attachment;filename=' . $filename);
        header('Content-Transfer-Encoding: binary');
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    public function getDisallowedCsvFields()
    {
        return [
            'created_at',
            'updated_at',
            'sku_type',
            'price_type',
            'weight_type',
            'shipment_type',
            'links_purchased_separately',
            'links_title',
            'price_view',
            'url_key',
            'url_path',
            'creator_id',
            'tax_class_id',
            'visibility',
            'status',
            'admin_product_note',
            'supplier_actived_product',
            'frontendproduct_product_status',
            'image',
            'image_small_image',
            'image_small_thumbnail',
            'options_container',
            'tier_price',
            'minimal_price',
            'msrp',
            'gift_message_available',
            'custom_layout_update',
            'msrp_display_actual_price_type',
            'has_options',
            'required_options',
            'small_image_label',
            'thumbnail_label',
            'custom_design_from',
            'meta_title',
            'custom_design_to',
            'custom_design',
            'meta_description',
            'custom_layout',
            'quantity_and_stock_status',
            'category_ids',
            'news_from_date',
            'news_to_date',
            'country_of_manufacture',
            'links_exist',
            'marketplace_fee',
            'marketplace_fee_type',
            'page_layout',
            'gallery',
            'meta_keyword',
            'image_label',
            'swatch_image',
            'thumbnail',
            'media_gallery',
            'small_image',
        ];
    }

    public function getSupplierLogo($supplierId = false)
    {
        if (!$supplierId) {
            $supplier = $this->getLoggedSupplier();
        } else {
            if (!$this->isSupplier($supplierId)) {
                new LocalizedException(__('This customer is not supplier'));
            }
            $supplier = $this->customerFactory->create()->load($supplierId);
        }

        $path = $this->directoryList->getUrlPath('media') . '/supplier_logos/';
        $path .= $supplier->getSupplierLogo();

        if (!file_exists($path) || !$supplier->getSupplierLogo()) {
            return false;
        } else {
            $url = $this->storeManager->getStore()->getBaseUrl() . $path;
            $url = str_replace('index.php/', '', $url);

            return $url;
        }
    }

    public function getSupplierRawPageUrl($customerId, $area = 'frontend')
    {
        $customerPathId = 'marketplace_vendor_url_' . $customerId;
        $urlRewrite = $this->urlRewrite->load($customerPathId, 'metadata');
        $supplierCustomUrl = '';

        if($customerId and $this->moduleManager->isOutputEnabled('Cminds_SupplierRedirection')) {
            $supplier = $this->customerFactory->create()->load($customerId);
            if($supplier->getId()) {
                $supplierCustomUrl = $supplier->getDomainUrl();
            }
        }

        if($supplierCustomUrl != '') {
            $baseUrl = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

                return $baseUrl.$supplierCustomUrl;
        } elseif (!$urlRewrite->getId()) {
            if ($area == 'adminhtml') {
                return $this->urlBuilder->getDirectUrl(
                    'marketplace/supplier/view/id/' . $customerId
                );
            } else {
                return $this->urlBuilder->getUrl(
                    'marketplace/supplier/view',
                    ['id' => $customerId]
                );
            }

        } else {
            $baseUrl = $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_WEB);

            return $baseUrl . $urlRewrite->getRequestPath();
        }
    }

    public function supplierPagesEnabled()
    {
        return $this->scopeConfig->getValue(
            'configuration_marketplace/configure/enable_supplier_pages'
        );
    }

    public function canUploadLogos()
    {
        return $this->scopeConfig->getValue(
            'configuration_marketplace/'
            . 'configure/'
            . 'allow_suppliers_upload_images'
        );
    }


    public function getSupplierProducts($id)
    {
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('creator_id', $id)
            ->addAttributeToFilter(
                'status',
                ['eq' => ProductStatus::STATUS_ENABLED]
            )
            ->addAttributeToFilter('is_saleable', ['like' => '1']);

        $collection->addWebsiteFilter();
        $collection->addMinimalPrice()->addFinalPrice()->addTaxPercents();

        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $collection->addAttributeToFilter(
            'status',
            ['in' => $this->productStatus->getVisibleStatusIds()]
        );

        $collection
            ->addAttributeToFilter('is_saleable', ['like' => '1']);

        return $collection;
    }

    public function getRowIdentifier(){
        return $this->productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';
    }

    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
    public function getSupplierName($supplier_id){
        $customer = $this->customerFactory->create()->load($supplier_id);
        return $customer->getName();
    }
}
