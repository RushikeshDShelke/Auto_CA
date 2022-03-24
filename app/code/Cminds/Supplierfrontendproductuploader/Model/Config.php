<?php

namespace Cminds\Supplierfrontendproductuploader\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cminds Supplierfrontendproductuploader config model.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Config
{
    /**
     * Scope config object.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager object.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Store id.
     *
     * @var int
     */
    protected $storeId;

    /**
     * Already fetched config values.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Object initialization.
     *
     * @param ScopeConfigInterface  $scopeConfig  Scope config object.
     * @param StoreManagerInterface $storeManager Store manager object.
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        $this->storeId = $this->getStoreId();
    }

    /**
     * Return store id.
     *
     * @return int
     */
    protected function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Return config field value.
     *
     * @param string $fieldKey Field key.
     *
     * @return mixed
     */
    protected function getConfigValue($fieldKey)
    {
        if (isset($this->config[$fieldKey]) === false) {
            $this->config[$fieldKey] = $this->scopeConfig->getValue(
                $fieldKey,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            );
        }

        return $this->config[$fieldKey];
    }

    /**
     * Return bool value depends if module is enabled in admin
     * configuration or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfigValue(
            'configuration/configure/module_enabled'
        );
    }

    /**
     * Return bool value depends of that if supplier products auto approval
     * functionality is enabled or not.
     *
     * @return bool
     */
    public function isProductsAutoApprovalEnabled()
    {
        return (bool)$this->getConfigValue(
            'configuration/configure/products_auto_approval'
        );
    }

    /**
     * Return bool value depends of that if supplier products approval flag
     * reset after product being edited functionality is enabled or not.
     *
     * @return bool
     */
    public function isProductsApprovalResetEnabled()
    {
        return (bool)$this->getConfigValue(
            'configuration/configure/products_approval_reset_after_edit'
        );
    }

    /**
     * Return bool value depends of that if supplier notification about ordered
     * products is enabled in store scope or not.
     *
     * @return bool
     */
    public function isSupplierOrderedProductsNotificationEnabled()
    {
        return (bool)$this->getConfigValue(
            'configuration/suppliers_notifications/notify_supplier_when_product_was_ordered'
        );
    }

    /**
     * Return bool value depends of that if supplier can configure
     * notification about ordered products or not.
     *
     * @return bool
     */
    public function isSupplierOrderedProductsNotificationConfigurationEnabled()
    {
        return (bool)$this->getConfigValue(
            'configuration/suppliers_notifications/ordered_products_configuration_enabled'
        );
    }
}
