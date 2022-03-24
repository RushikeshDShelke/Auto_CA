<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Import;

use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Helper\Email;
use Magento\Backend\Model\Session;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Attribute\Management;
use Magento\Catalog\Model\ProductFactory as CatalogProduct;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Products extends AbstractController
{
    private $_usedImagesPaths = [];
    private $_setMainPhoto;
    private $_uploadedImagesCount;

    protected $attributeManagement;
    protected $eavAttribute;
    protected $entity;
    protected $backendSession;
    protected $registry;
    protected $customer;
    protected $helperEmail;
    protected $storeId;
    protected $product;
    protected $websiteId;
    protected $productAction;
    protected $category;
    protected $eavConfig;
    protected $helper;

    public function __construct(
        Context $context,
        Data $helper,
        Management $management,
        EavAttribute $attribute,
        Entity $entity,
        Session $backendSession,
        Registry $registry,
        Customer $customer,
        Email $emailHelper,
        CatalogProduct $product,
        ProductAction $productAction,
        Category $category,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->attributeManagement = $management;
        $this->eavAttribute = $attribute;
        $this->entity = $entity;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
        $this->customer = $customer;
        $this->helperEmail = $emailHelper;
        $this->product = $product;
        $this->productAction = $productAction;
        $this->category = $category;
        $this->eavConfig = $eavConfig;
        $this->helper = $helper;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $this->_view->loadLayout();

        $value = (int)$this->scopeConfig->getValue(
            'products_settings/csv_import/enable_csv_import'
        );
        if ($value === 1) {
            $this->_handleUpload();
            $this->renderBlocks();
            $this->_view->renderLayout();
        } else {
            $this->force404();
        }
    }

    private function _handleUpload()
    {
        $files = $this->getRequest()->getFiles();

        if (isset($files['file']['name']) && ($files['file']['tmp_name'] != null)) {
            if (!$this->_validateSalt()) {
                return false;
            }

            $importResponse = [];
            $successCount = 0;
            $i = 0;
            $headers = [];
            $this->_uploadedImagesCount = 0;

            if (($handle = fopen($files['file']['tmp_name'], 'r')) !== false) {
                if (is_int($this->scopeConfig->getValue('configuration/csv_import/how_many_product_can_be_imported'))
                    && $this->scopeConfig->getValue('configuration/csv_import/how_many_product_can_be_imported') > 0
                    && count(file($files['file']['tmp_name'])) > $this->scopeConfig->getValue('configuration/csv_import/how_many_product_can_be_imported') + 1
                ) {
                    $this->messageManager->addErrorMessage(
                        __('Too many products added to import.')
                    );
                } else {
                    $this->storeId = $this->storeManager
                        ->getStore()
                        ->getId();
                    $this->websiteId = $this->storeManager
                        ->getStore()
                        ->getWebsiteId();

                    while (($data = fgetcsv($handle)) !== false) {
                        if ($i != 0) {
                            $res = $this->_parseCsv($data, $headers);
                            if ($res['success']) {
                                $successCount++;
                            }
                            $res['line'] = $i;
                            $importResponse[] = $res;
                        } else {
                            $s = $this->validateHeaders($data);
                            if (count($s) > 0) {
                                $this->messageManager->addError(
                                    __(
                                        'Attributes doesn\'t match all '
                                        . 'required attributes. '
                                        . 'Missing attribute : ' . $s[0]
                                    )
                                );
                                break;
                            }
                            $headers = $data;
                        }
                        $i++;
                    }
                    fclose($handle);
                }
            }
            $this->registry->register('import_data', $importResponse);

            $this->registry->register('upload_done', true);
            $attributeSetId = $this->getRequest()->getParam('attributeSetId');
            $this->registry->register('attributeSetId', $attributeSetId);
        }
    }

    private function _parseCsv($line, $headers)
    {
        $this->storeManager->setCurrentStore($this->storeId);

        try {
            $this->_setMainPhoto = false;
            $productModel = $this->_findProduct($headers, $line);
            $isNew = false;
            if (!$productModel) {
                $isNew = true;
                $productModel = $this->product->create();
                $productModel->setTypeId('simple');
                $productModel->setWebsiteIds([$this->websiteId]);

                $attributeSetId = $this->getRequest()->getParam('attributeSetId');
                $productModel->setAttributeSetId($attributeSetId);
                $productModel->setStatus(
                    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                );
                $productModel->setVisibility(
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
                );
                $productModel->setTaxClassId(
                    $this->scopeConfig->getValue(
                        'products_settings/adding_products/product_tax_class'
                    )
                );
                $productModel->setData(
                    'frontendproduct_product_status',
                    \Cminds\Supplierfrontendproductuploader\Model\Product::STATUS_PENDING
                );
                $productModel->setData(
                    'creator_id',
                    $this->helper->getSupplierId()
                );

                if (!$this->scopeConfig->getValue('products_settings/adding_products/supplier_can_define_sku') == 2) {
                    $productModel->setSku($this->helper->generateSku());
                }
            }
            $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

            $foundCategories = false;

            foreach ($headers as $i => $header) {
                $missLine = false;
                $attributeCode = trim($this->_prepareHeader($header));

                if (isset($line[$i])) {
                    if (strtolower($attributeCode) === 'category'
                        && $line[$i] != ""
                    ) {
                        $foundCategories = true;
                        $missLine = true;
                        $categories = $this->_validateCategories($line[$i]);
                        $productModel->setCategoryIds($categories);
                    }

                    $value = $this->_validateAttributeValue(
                        $attributeCode,
                        $line[$i]
                    );

                    if (strtolower($attributeCode) === 'qty') {
                        $productModel->setStockData(
                            [
                                'is_in_stock' => ($line[$i] > 0) ? 1 : 0,
                                'qty' => $line[$i],
                            ]
                        );
                    }

                    if (strtolower($attributeCode) === 'image') {
                        if (!$this->canUploadImage()) {
                            continue;
                        }

                        $key = $this->_findImageFileName($line[$i]);
                        $path = $this->_uploadImage($key);

                        if ($path && file_exists($path)) {
                            $attrs = null;

                            if (!$this->_setMainPhoto) {
                                $attrs = [
                                    'image',
                                    'small_image',
                                    'thumbnail',
                                ];
                                $this->_setMainPhoto = true;
                            }
                            $productModel->addImageToMediaGallery(
                                $path,
                                $attrs,
                                false,
                                false
                            );

                            $this->_uploadedImagesCount++;
                        }
                    }
                    if (!$missLine) {
                        if ($value) {
                            $productModel->setData($attributeCode, $value);
                        } else {
                            $productModel->setData($attributeCode, $line[$i]);
                        }
                    }
                } else {
                    if ($this->_isRequired($attributeCode)) {
                        throw new \Exception(
                            __(
                                "Value for attribute : %s is not valid",
                                $attributeCode
                            )
                        );
                    }
                }
            }

            if (!$foundCategories) {
                throw new \Exception(__('No categories found'));
            }
            $autoApprove = $this->scopeConfig
                ->getValue('configuration/configure/products_auto_approval');
            if ($autoApprove) {
                $p = $productModel;
                $p->setVisibility(
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                );
                $p->setData(
                    'frontendproduct_product_status',
                    \Cminds\Supplierfrontendproductuploader\Model\Product::STATUS_APPROVED
                );
            }
            $productModel->save();

            if ($isNew) {
                $mediaGallery = $productModel->getMediaGallery();
                if (isset($mediaGallery['images'])) {
                    foreach ($mediaGallery['images'] as $image) {
                        $this->productAction->updateAttributes(
                            [$productModel->getId()],
                            ['image' => $image['file']],
                            0
                        );
                        break;
                    }
                }
            }
            $this->_removeUsedImages();

            return [
                'success' => true,
                'product_id' => $productModel->getId(),
                'sku' => $productModel->getSku(),
                'product_name' => $productModel->getName(),
            ];
        } catch (\Exception $e) {
            $this->_removeUsedImages();

            if (method_exists($e, 'getAttributeCode')) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'attribute_code' => $e->getAttributeCode(),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'attribute_code' => 'unknown',
                ];
            }
        }
    }

    /**
     * Check if allowed maximum of uploaded images is reached.
     * If the maximum is reached, then return false - we don't allow to upload more images.
     * In other situation return true.
     *
     * @return bool
     */
    protected function canUploadImage()
    {
        $allowedMax = (int)$this->scopeConfig
            ->getValue('products_settings/adding_products/maximum_allowed_images');

        if (!$allowedMax || !is_int($allowedMax) || $allowedMax === 0) {
            return false;
        }

        if ($this->_uploadedImagesCount < $allowedMax) {
            return true;
        }

        return false;
    }

    protected function _findProduct($headers, $line)
    {
        $foundIdValue = false;
        foreach ($headers as $i => $header) {
            if (strtolower($header) == 'id') {
                $foundIdValue = $line[$i];
                break;
            }
        }

        if (!$foundIdValue || !is_numeric($foundIdValue)) {
            return false;
        }
        $product = $this->product->create()->load($foundIdValue);

        if (!$product->getId()) {
            throw new \Exception(__("Product does not exists"));
        }

        if ($product->getCreatorId() != $this->helper->getSupplierId()) {
            throw new \Exception(__("Product does not exists"));
        }

        return $product;
    }

    private function _validateCategories($categories_ids)
    {
        $categories = explode(';', $categories_ids);
        $validCategoriesIds = [];

        $isValid = false;
        foreach ($categories as $category) {
            $categoryModel = $this->category->loadByAttribute(
                'name',
                $category
            );
            if ($categoryModel && $categoryModel->getId()) {
                $isValid = true;
                $validCategoriesIds[] = $categoryModel->getId();
            }
        }

        if (!$isValid) {
            throw new \Exception(__('No valid category'));
        }

        return $validCategoriesIds;
    }

    private function _prepareHeader($header)
    {
        return str_replace(' (*)', '', $header);
    }

    private function _isRequired($attribute_code)
    {
        $attributeModel = $this->eavConfig->getAttribute(
            'catalog_product',
            $attribute_code
        );

        return $attributeModel->getIsRequired();
    }

    private function _validateAttributeValue($attribute_code, $value)
    {
        $attributeModel = $this->eavConfig->getAttribute(
            'catalog_product',
            $attribute_code
        );

        if ($attributeModel->getIsRequired() && $value == '') {
            throw new \Exception("Attribute " . $attribute_code . " is required");
        }

        if ($attributeModel->getFrontendInput() == 'select') {
            if ($value != '') {
                $attribute = $this->eavAttribute->load($attributeModel->getId());
                $attributeOptions = $attribute->getSource()->getAllOptions(false);
                $availableLabels = [];

                foreach ($attributeOptions as $attributeOption) {
                    $availableLabels[strtolower($attributeOption['label'])]
                        = $attributeOption['value'];
                }

                if (count($availableLabels) > 0) {
                    if (!in_array(
                        strtolower($value),
                        array_keys($availableLabels)
                    )
                    ) {
                        throw new \Exception(
                            "Value of attribute " . $attribute_code
                            . " is not valid . Value : " . $value
                        );
                    }
                }

                return $availableLabels[strtolower($value)];
            }
        }

        if ($attributeModel->getBackendType() == 'decimal') {
            if ($value != "" && !is_numeric($value)) {
                throw new \Exception(
                    "Value of attribute " . $attribute_code
                    . " is not valid. Should be numeric."
                );
            }
        }

        return false;
    }

    public function validateHeaders($headers)
    {
        $attributes = $this->attributeManagement
            ->getAttributes(
                $this->scopeConfig->getValue(
                    'products_settings/adding_products/attributes_set'
                )
            );

        $required = [];

        /**
         * Internal
         */
        $headers[] = 'created_at';
        $headers[] = 'sku';
        $headers[] = 'sku_type';
        $headers[] = 'status';
        $headers[] = 'tax_class_id';
        $headers[] = 'updated_at';
        $headers[] = 'visibility';
        $headers[] = 'shipment_type';
        $headers[] = 'weight_type';
        $headers[] = 'price_type';
        $headers[] = 'price_view';
        $headers[] = 'weight_type';
        $headers[] = 'links_purchased_separately';
        $headers[] = 'links_title';

        foreach ($attributes as $attribute) {
            if ($attribute['required']) {
                $required[] = $attribute['code'];
            }
        }

        foreach ($headers as $k => $header) {
            $headers[$k] = $this->_prepareHeader($header);
        }

        return array_values(array_diff($required, $headers));
    }

    private function downloadImage($url)
    {
        set_time_limit(0);
        $dir = $this->helper->getImageCacheDir();
        $lfile = fopen($dir . '/' . basename($url), "w");

        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $url,
                CURLOPT_BINARYTRANSFER => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FILE => $lfile,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            ]
        );

        $results = curl_exec($ch);
        if ($results) {
            return $dir . '/' . basename($url);
        }

        return false;
    }

    private function _uploadImage($key)
    {
        $files = $this->getRequest()->getFiles();
        if (count($files['files']) == 0) {
            return false;
        }

        $file = [
            'name' => $files['files'][$key]['name'],
            'type' => $files['files'][$key]['type'],
            'tmp_name' => $files['files'][$key]['tmp_name'],
            'error' => $files['files'][$key]['error'],
            'size' => $files['files'][$key]['size'],
        ];

        $path = $this->helper->getImageCacheDir();

        try {
            $uploader = new \Magento\Framework\File\Uploader($file);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $res = $uploader->save($path, $file['name']);
            $this->_usedImagesPaths[] = $path . '/' . $res['name'];

            return $path . '/' . $res['name'];
        } catch (\Exception $e) {
            return false;
        }
    }

    private function _removeUsedImages()
    {
        foreach ($this->_usedImagesPaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function _findImageFileName($name)
    {
        $files = $this->getRequest()->getFiles();

        foreach ($files['files'] as $key => $file) {
            if ($name == $file['name']) {
                return $key;
            }
        }

        return false;
    }

    private function _validateSalt()
    {
        $salt = $this->getRequest()->getParam('salt');

        $sessionSalt = $this->backendSession->getMarketplaceImportSalt();

        if ($salt != $sessionSalt) {
            $this->backendSession->setMarketplaceImportSalt($salt);

            return true;
        }

        return false;
    }
}
