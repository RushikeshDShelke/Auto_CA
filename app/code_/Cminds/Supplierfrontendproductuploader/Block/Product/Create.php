<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Product;

use Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Categories\CollectionFactory as RestrictedCategoryCollectionFactory;
use Cminds\Supplierfrontendproductuploader\Block\Product as ProductBlock;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Attribute as AttributeHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Product\Media\Video;
use Cminds\Supplierfrontendproductuploader\Model\Labels as SupplierLabels;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Management as AttributeManagement;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory as CategoryTreeFactory;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Downloadable\Model\LinkFactory as DownloadableLinkFactory;
use Magento\Eav\Model\ConfigFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\PriceCurrency;
/* inventory check */
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class Create extends ProductBlock
{
    protected $selectedCategories;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryTreeFactory
     */
    protected $categoryTreeFactory;

    /**
     * @var AttributeHelper
     */
    protected $attributeHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var SupplierHelper
     */
    protected $supplierHelper;

    /**
     * @var Http
     */
    protected $httpRequest;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var SupplierLabels
     */
    protected $supplierLabels;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var AttributeManagement
     */
    protected $attributeManagement;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var DownloadableLinkFactory
     */
    protected $downloadableLinkFactory;

    /**
     * @var Video
     */
    protected $videoHelper;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var RestrictedCategoryCollectionFactory
     */
    protected $restrictedCategoryCollectionFactory;

    /**
     * @var ProductUploaderInventory
     */
    private $productUploaderInventory;

    protected $priceHelper;

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
        DirectoryList $directoryList,
        RestrictedCategoryCollectionFactory $restrictedCategoryCollectionFactory,
        Price $priceHelper,
        ProductUploaderInventory $productUploaderInventory
    ) {
        parent::__construct(
            $registry,
            $context,
            $productFactory,
            $video
        );

        $this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryTreeFactory = $categoryTreeFactory;
        $this->attributeFactory = $attributeFactory;
        $this->attributeHelper = $attributeHelper;
        $this->scopeConfigInterface = $context->getScopeConfig();
        $this->supplierHelper = $supplierHelper;
        $this->httpRequest = $httpRequest;
        $this->supplierLabels = $supplierLabels;
        $this->customerSession = $customerSession;
        $this->attributeManagement = $attributeManagement;
        $this->configFactory = $configFactory;
        $this->downloadableLinkFactory = $downloadableLinkFactory;
        $this->stockState = $stockState;
        $this->directoryList = $directoryList;
        $this->restrictedCategoryCollectionFactory = $restrictedCategoryCollectionFactory;
        $this->priceHelper = $priceHelper;

        $this->productUploaderInventory = $productUploaderInventory;
    }

    /**
     * Set categories to select in form.
     *
     * @param $categories
     */
    public function setSelectedCategories($categories)
    {
        $this->selectedCategories = $categories;
    }

    /**
     * Get store categories.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        $parent = $this->_storeManager->getStore()->getRootCategoryId();
        $category = $this->categoryFactory->create();

        if (!$category->checkId($parent)) {
            return false;
        }

        $supplierId = $this->customerSession->getId();
        $restrictedCategoriesCollection = $this->restrictedCategoryCollectionFactory
            ->create();
        $restrictedCategoriesCollection
            ->addFieldToFilter('supplier_id', $supplierId);

        $restrictedCategoryIds = [];
        foreach ($restrictedCategoriesCollection as $restrictedCategory) {
            $restrictedCategoryIds[] = $restrictedCategory->getCategoryId();
        }

        $storeCategories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter(
                'available_for_supplier',
                [
                    ['eq' => 1],
                ]
            )
            ->addAttributeToFilter(
                'entity_id',
                [
                    'neq' => Category::TREE_ROOT_ID,
                ]
            )
            ->addAttributeToSort('path');

        if (!empty($restrictedCategoryIds)) {
            $storeCategories
                ->addAttributeToFilter(
                    'entity_id',
                    [
                        'nin' => $restrictedCategoryIds,
                    ]
                );
        }

        return $storeCategories;
    }

    /**
     * Get available attributes sets for suppliers.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    public function getAvailableAttributeSets()
    {
        return $this->attributeHelper->getAvailableAttributeSets();
    }

    /**
     *  Get available products types for suppliers.
     *
     * @return array
     */
    public function getProductTypes()
    {
        $types = [
            [
                'label' => __('Simple Product'),
                'value' => Type::TYPE_SIMPLE,
            ],
        ];

        if ($this->supplierHelper->canCreateVirtualProduct()) {
            $types[] = [
                'label' => __('Virtual Product'),
                'value' => Type::TYPE_VIRTUAL,
            ];
        }

        if ($this->supplierHelper->canCreateConfigurableProduct()) {
            $types[] = [
                'label' => __('Configurable Product'),
                'value' => Configurable::TYPE_CODE,
            ];
        }

        if ($this->supplierHelper->canCreateGroupedProduct()) {
            $types[] = [
                'label' => __('Grouped Product'),
                'value' => Grouped::TYPE_CODE,
            ];
        }

        if ($this->supplierHelper->canCreateDownloadableProduct()) {
            $types[] = [
                'label' => __('Downloadable Product'),
                'value' => Downloadable::TYPE_DOWNLOADABLE,
            ];
        }

        return $types;
    }

    /**
     * Get current product type id.
     *
     * @return int
     */
    public function getProductTypeId()
    {
        $params = $this->_request->getParams();

        return $params['type'];
    }

    /**
     * Get attribute set id.
     *
     * @param $product
     *
     * @return int
     */
    public function getAttributeSetId($product = null)
    {
        if (!empty($product)) {
            return $product->getAttributeSetId();
        }
        $params = $this->_request->getParams();

        if (!isset($params['attribute_set_id'])
            || empty($params['attribute_set_id'])
        ) {
            $configAttributeSet = $this->scopeConfigInterface
                ->getValue(
                    $this->attributeHelper->getDefaultSetConfigKey()
                );
        } else {
            $configAttributeSet = $params['attribute_set_id'];
        }

        return $configAttributeSet;
    }

    /**
     * Get categories nodes.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $categories
     *
     * @return string
     */
    public function getNodes($categories)
    {
        $html = '';

        foreach ($categories as $category) {
            if ($category->getAvailableForSupplier() === 0) {
                continue;
            }

            $html .= $this->renderCategory($category);
        }

        return $html;
    }

    /**
     * Render category node.
     *
     * @param $category
     *
     * @return string
     */
    protected function renderCategory($category)
    {
        $inArray = in_array($category->getId(), $this->selectedCategories);

        $html = '<li class="level-' . $category->getLevel() . '" '
            . 'style="margin-left:' . (15 * $category->getLevel()) . 'px">';
        $html .= '<input id="category-' . $category->getId(). '" class="category_checkbox required-entry" type="checkbox" name="category[]" '
            . 'value="' . $category->getId() . '"'
            . ($inArray ? ' checked' : '') . '/> ';
        $html .= '<label style="font-weight:normal;" for="category-' . $category->getId() . '">' . $category->getName() . '</label>';
        $html .= '</li>';

        return $html;
    }

    /**
     * Get attributes for current attribute set.
     *
     * @param $product
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributes($product = null)
    {
        $configAttributeSet = $this->getAttributeSetId($product);

        $attributes = $this->attributeManagement
            ->getAttributes($configAttributeSet);

        return $attributes;
    }

    /**
     * Get assigned attributes.
     *
     * @param Product $product
     *
     * @return array
     */
    public function getAssignedAttributesIds($product)
    {
        $assignedAttributes = $product->getTypeInstance()->getConfigurableOptions($product);

        return $assignedAttributes;
    }

    /**
     * Get attribute input.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    public function getAttributeHtml($attribute, $data = null)
    {
        $frontend = $attribute->getFrontend();

        switch ($frontend->getInputType()) {
            case 'text':
                return $this->getTextField($attribute, $data);
                break;
            case 'textarea':
                return $this->getTextareaField($attribute, $data);
                break;
            case 'price':
                return $this->getTextField($attribute, $data);
                break;
            case 'date':
                return $this->getDateField($attribute, $data);
                break;
            case 'select':
                return $this->getSelectField($attribute, $data);
                break;
            case 'multiselect':
                return $this->getSelectField($attribute, $data, true);
                break;
            case 'boolean':
                return $this->getBooleanField($attribute, $data, true);
                break;
            default:
                return $frontend->getInputType();
                break;
        }
    }

    /**
     * Get attribute text field.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    protected function getTextField($attribute, $data)
    {
        $value = isset($data[$attribute->getAttributeCode()])
            ? $data[$attribute->getAttributeCode()] :
            null;

        return '<input type="text" value="' . $value
            . '" name="' . $attribute->getAttributeCode()
            . '" class="' . $attribute->getFrontend()->getClass()
            . ' form-control">';
    }

    /**
     * Get attribute textarea field.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    protected function getTextareaField($attribute, $data)
    {
        $value = isset($data[$attribute->getAttributeCode()])
            ? $data[$attribute->getAttributeCode()]
            : null;

        return '<textarea class="form-control" '
            . 'name="' . $attribute->getAttributeCode() . '">'
            . $value . '</textarea>';
    }

    /**
     * Get attribute date field.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    protected function getDateField($attribute, $data)
    {
        $value = isset($data[$attribute->getAttributeCode()])
            ? $data[$attribute->getAttributeCode()]
            : null;

        return '<input type="text" value="' . $value
            . '" name="' . $attribute->getAttributeCode()
            . '" value="' . $value . '" class="datepicker '
            . $attribute->getFrontend()->getClass() . '">';
    }

    /**
     * Get attribute select field.
     *
     * @param $attribute
     * @param $data
     * @param $isMultiple
     *
     * @return string
     */
    protected function getSelectField($attribute, $data, $isMultiple = false)
    {
        $value = isset($data[$attribute->getAttributeCode()])
            ? $data[$attribute->getAttributeCode()]
            : null;

        if (strstr($value, ', ')) {
            $value = explode(', ', $value);
        }

        $isMultiple = ($isMultiple) ? " multiple" : "";
        $isMultipleStyle = ($isMultiple) ? " height:150px;" : "";
        $name = $attribute->getAttributeCode();
        $name .= ($isMultiple) ? "[]" : "";

        $html = '<select name="' . $name . '" '
            . 'style="' . $isMultipleStyle . '" class="form-control '
            . $attribute->getFrontend()->getClass() . '"' . $isMultiple . '>';

        $allOptions = $attribute->getSource()->getAllOptions(false);
        $html .= '<option value="">----------------</option>';

        foreach ($allOptions as $option) {
            if ($option['value'] === '') {
                continue;
            }

            if (!is_array($option['value'])) {
                $selected = ($value === $option['value']
                    || $value === $option['label']);
            } else {
                $selected = (in_array($option['value'], $value)
                    || in_array($option['label'], $value));
            }

            $html .= '<option value="' . $option['value'] . '" '
                . ($selected ? '  selected="selected"' : '') . '>'
                . $option['label'] . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Get attribute boolen field.
     *
     * @param $attribute
     * @param $data
     *
     * @return string
     */
    protected function getBooleanField($attribute, $data)
    {
        $value = isset($data[$attribute->getAttributeCode()])
            ? $data[$attribute->getAttributeCode()]
            : $attribute->getDefaultValue();

        $html = '<select name="' . $attribute->getAttributeCode()
            . '" class="form-control '
            . $attribute->getFrontend()->getClass() . '">';

        $allOptions = $attribute->getSource()->getAllOptions();

        foreach ($allOptions as $option) {
            $selected = false;
            if ($value === $option['value']) {
                $selected = true;
            } elseif (is_array($value) && in_array($option['value'], $value)) {
                $selected = true;
            } elseif ($value === $option['label']) {
                $selected = true;
            } elseif (is_array($value) && in_array($option['label'], $value)) {
                $selected = true;
            }

            $html .= '<option value="' . $option['value'] . '" '
                . (($selected) ? ' selected="selected"' : '') . '>'
                . $option['label'] . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Check possible to set manualu SKU.
     *
     * @return bool
     */
    public function canAddSku()
    {
        $canAddSku = $this->scopeConfigInterface->getValue(
            'products_settings/adding_products/supplier_can_define_sku'
        );

        return (bool)((int)$canAddSku === 2);
    }

    /**
     * Get maximum possible images for product.
     *
     * @return int
     */
    public function getMaxImagesCount()
    {
        $imagesCount = $this->scopeConfigInterface->getValue(
            'products_settings/adding_products/maximum_allowed_images'
        );

        return $imagesCount;
    }

    /**
     * Check possible to add images to product.
     *
     * @return bool
     */
    public function canAddImages()
    {
        $imagesCount = $this->scopeConfigInterface->getValue(
            'products_settings/adding_products/allow_suppliers_upload_images'
        );

        return (bool)$imagesCount;
    }

    /**
     * Get label for attribute.
     *
     * @param $attribute
     * @param $force
     * @param $loaded
     *
     * @return Phrase|string
     */
    public function getLabel($attribute, $force = '', $loaded = true)
    {
        if (!$loaded) {
            $attribute = $this->attributeFactory->create()
                ->loadByCode(4, $attribute);
        }

        if (!is_object($attribute)) {
            return $force;
        }

        $label = $this->supplierLabels
            ->load($attribute->getAttributeCode(), 'attribute_code');

        if ($label->getId() === null) {
            if ($force != '' && $force != null) {
                return __($force);
            } else {
                return $attribute->getFrontend()->getLabel();
            }
        } else {
            if ($label->getLabel() === '' || $label->getLabel() === null) {
                if ($force != '' && $force != null) {
                    return __($force);
                } else {
                    return $attribute->getFrontend()->getLabel();
                }
            } else {
                return $label->getLabel();
            }
        }
    }

    /**
     * Get params from request.
     *
     * @return array
     */
    public function getDataParams()
    {
        return $this->_request->getParams();
    }

    /**
     * Get product attribute instance by code.
     *
     * @param $code
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttribute($code)
    {
        return $this->configFactory->create()
            ->getAttribute(Product::ENTITY, $code);
    }

    /**
     * Get media URL.
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get real path to media.
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->directoryList->getPath('media');
    }

    /**
     * Get product qty.
     *
     * @param $productId
     *
     * @return float
     */
    public function getQty($productId)
    {
        $product = $this->productFactory->create()
            ->load($productId);

        return $this->stockState->getStockQty(
            $productId,
            $product->getStore()->getWebsiteId()
        );
    }

    /**
     * Get downloadable links collection.
     *
     * @return $this
     */
    public function getLinks()
    {
        $links = $this->downloadableLinkFactory->create()
            ->getCollection()
            ->addTitleToResult()
            ->addFieldToFilter(
                'product_id',
                ['eq' => $this->getProduct()->getId()]
            )
            ->load();

        return $links;
    }

    /**
     * Get downloadable link for the file.
     *
     * @param LinkInterface $link
     *
     * @return string
     */
    public function getLinkDownloadUrl(LinkInterface $link)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $urlBuilder = $objectManager->create(UrlInterface::class);

        $url = $urlBuilder->addSessionParam()->getUrl(
            'supplier/download/uploadedFile',
            ['id' => $link->getId(), '_secure' => true]
        );

        return $url;
    }

    /**
     * Get formatted file name.
     *
     * @param $fileName
     *
     * @return Phrase|mixed
     */
    public function getFormatedFileName($fileName)
    {
        if (!$fileName) {
            return __('File');
        }

        $file = explode('/', $fileName);

        return end($file);
    }

    /**
     * Get current currency symbol.
     *
     * @return null|string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->priceHelper->getCurrentCurrencySymbol();
    }

    /**
     * Get Store Price.
     *
     * @param $price
     *
     * @return null
     */
    public function getStorePrice($price)
    {
        if (!$price) {
            return null;
        }

        return $this->priceHelper->convertToCurrentCurrencyPrice($price);
    }

    /**
     * get sources array for product creation.
     *
     * @return array
     */
    public function getInventorySourcesArray()
    {
        $searchResult = $this->productUploaderInventory->getInventorySourcesFilteredArray();
        $sources = [];

        foreach ($searchResult as $key => $source) {
            $sources[] = [
                'id' => $key,
                'code' => $source->getSourceCode(),
                'name' => $source->getName(),
                'enabled' => $source->isEnabled(),
            ];
        }
        return $sources;
    }

    /**
     * Check if mulriple inventory sources are configured.
     *
     * @return bool
     */
    public function inventoryIsSingleSourceMode()
    {
        return $this->productUploaderInventory->inventoryIsSingleSourceMode();
    }

    /**
     * get product sources.
     *
     * @return array
     */
    public function getInventorySourcesBySku($sku)
    {
        $sources = $this->productUploaderInventory->getSourcesBySku((string) $sku);

        $sourcesDataArray = [];
        if ($sources) {
            foreach ($sources as $source) {
                $item = [];
                $item['code'] = $source->getSourceCode();
                $inventorySource = $this->productUploaderInventory
                    ->getInventorySourceByCode($source->getSourceCode());
                $item['qty'] = $source->getQuantity();
                $item['name'] = $inventorySource->getName();
                $item['status'] = $source->getStatus();
                $item['enabled'] = $inventorySource->isEnabled();

                $sourcesDataArray[] = $item;
            }
        }

        return $sourcesDataArray;
    }
}
