<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Helper;

use Dotzotfront\ShippingTracking\Helper\Data;

/**
 * Class Config use for retrieve module configuration
 */
class Config extends \Dotzotfront\ShippingTracking\Helper\Main
{
    /**
     * UPS Group Id
     */
    const UPS_API_GROUP = 'ups_api';

    /**
     * Fedex Group Id
     */
    const FEDEX_API_GROUP = 'fedex_api';

    /**
     * USPS Group Id
     */
    const USPS_API_GROUP = 'usps_api';

    /**
     * Service Group Id
     */
    const SERVICE_GROUP = 'services';

    /**
     * Fedex Id
     */
    const FEDEX = 'fedex';

    /**
     * Ups Id
     */
    const UPS = 'ups';

    /**
     * Usps Id
     */
    const USPS = 'usps';

    /**
     * Test Connection Url
     */
    const TEST_CONNECTION_URL = 'prshippingtracking/test/index';

    /**
     * Retrieve config value according to current section identifier
     *
     * @param string $path
     * @param string|int $store
     * @return mixed
     */
    private function getConfigForCurrentSection($path, $store = null)
    {
        return $this->getConfig(
            Data::SECTION_ID  . '/'. $path,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function enabledUpsApi($store = null)
    {
        return (bool)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::UPS_API_GROUP . '/enabled',
            $store
        );
    }

    /**
     * @param      $carrier
     * @param null $store
     * @return bool
     */
    public function getIconByName($carrier, $store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . $carrier . '_api' . '/' . $carrier . '_icon',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getUserIdUpsApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::UPS_API_GROUP . '/user_id',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getPasswordUpsApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::UPS_API_GROUP . '/password',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getApiKeyUpsApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::UPS_API_GROUP . '/api_key',
            $store
        );
    }

    /**
     * @param $store
     * @return string
     */
    public function getSandboxUpsApi($store)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::UPS_API_GROUP . '/sandbox_mode',
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function enabledFedexApi($store = null)
    {
        return (bool)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::FEDEX_API_GROUP . '/enabled',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getAccountNumberFedexApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::FEDEX_API_GROUP . '/account_number',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getMeterNumberFedexApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::FEDEX_API_GROUP . '/meter_number',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getKeyFedexApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::FEDEX_API_GROUP . '/key',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getPasswordFedexApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::FEDEX_API_GROUP . '/password',
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getSandboxModeFedexApi($store = null)
    {
        return (bool)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::FEDEX_API_GROUP . '/sandbox_mode',
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function enabledUspsApi($store = null)
    {
        return (bool)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::USPS_API_GROUP . '/enabled',
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getUserIdUspsApi($store = null)
    {
        return (string)$this->getConfigForCurrentSection(
            self::SERVICE_GROUP . '/' . self::USPS_API_GROUP . '/user_id',
            $store
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    public function getEnabledMethodByName($name = '', $store = null)
    {
        switch ($name) {
            case self::FEDEX:
                $result = $this->enabledFedexApi($store);
                break;
            case self::UPS:
                $result = $this->enabledUpsApi($store);
                break;
            case self::USPS:
                $result = $this->enabledUspsApi($store);
                break;
            default:
                $result = false;
        }

        return $result;
    }
}