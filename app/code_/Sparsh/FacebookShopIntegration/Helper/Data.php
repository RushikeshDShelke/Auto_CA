<?php
/**
 * Class Data
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Module config path
     */
    const CONFIG_MODULE_PATH = 'facebook_shop_integration/';

    /**
     * Get Config value
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return config value for apply catalog rule on prices or not
     *
     * @return mixed
     */
    public function getApplyCatalogRules()
    {
        return $this->getConfigValue(
            self::CONFIG_MODULE_PATH .'general/apply_catalog_price_rules'
        );
    }

    /**
     * Return config value for add out of stock products in csv or not
     *
     * @return mixed
     */
    public function getAddOutOfStockProducts()
    {
        return $this->getConfigValue(
            self::CONFIG_MODULE_PATH .'general/add_out_of_stock_products'
        );
    }

    /**
     * Return config value for generate csv on schedule is enabled or not
     *
     * @return mixed
     */
    public function getIsCsvScheduled()
    {
        return $this->getConfigValue(
            self::CONFIG_MODULE_PATH .'general/schedule_generate_csv'
        );
    }
}
