<?php

namespace Cminds\Marketplace\Helper;

use Cminds\Supplierfrontendproductuploader\Helper\Data as DataParent;
use Magento\Catalog\Model\ProductFactory;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class Data extends DataParent
{
    public function getAllShippingMethods()
    {
        $methods = [];
        $config = $this->scopeConfig->getValue('carriers');
        foreach ($config as $code => $methodConfig) {
            if (!isset($methodConfig['title'])) {
                continue;
            }
            $methods[$code] = $methodConfig['title'];
        }

        return $methods;
    }

    public function hasAccess()
    {
        return true;
    }

    public function getSupplierPageUrl($product)
    {
        if ($product->getCreatorId()) {
            return $this->getSupplierRawPageUrl($product->getCreatorId());
        }
    }
    public function setSupplierDataInstalled($installed)
    {
        mail(
            'david@cminds.com',
            'Marketplace installed',
            'IP: ' . $_SERVER['SERVER_ADDR'] . ' host : ' . $_SERVER['SERVER_NAME']
        );
    }

    public function canCreateConfigurable()
    {
        return $this->scopeConfig->getValue(
            'configuration/presentation/allow_create_configurable'
        );
    }

    /**
     * Get store config if supplier can manage his shipping methods.
     *
     * @return bool
     */
    public function shippingMethodsEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'configuration_marketplace/presentation/allow_supplier_manage_shipping_costs'
        );
    }

    public function csvImportEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'products_settings/csv_import/enable_csv_import'
        );
    }

    public function array2Csv(array $data)
    {
        if (count($data) === 0) {
            return null;
        }

        ob_start();
        $df = fopen('php://output', 'wb');
        fputcsv($df, array_keys(reset($data)));
        foreach ($data as $row) {
            foreach ($row as $column => &$value) {
                $value = ' ' . $value;
            }
            unset($value);
            fputcsv($df, $row);
        }
        fclose($df);

        return ob_get_clean();
    }

    public function getStatusesCanSee()
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                'configuration_marketplace/'
                . 'presentation/'
                . 'order_statuses_supplier_can_see'
            )
        );
    }

    /**
     * Get store config if supplier shipping method is enabled.
     *
     * @return bool
     */
    public function isSupplierShippingMethodEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'carriers/supplier/active'
        );
    }
}
