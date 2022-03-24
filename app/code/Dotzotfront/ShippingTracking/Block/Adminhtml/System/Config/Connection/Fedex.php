<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Block\Adminhtml\System\Config\Connection;

use Dotzotfront\ShippingTracking\Helper\Config;

class Fedex extends \Dotzotfront\ShippingTracking\Block\Adminhtml\System\Config\Connection\AbstractButton
{
    /**
     * Service prefix
     */
    const SERVICE_PREFIX = 'prshippingtracking_services_fedex_api_';

    /**
     * @param null $htmlId
     * @return string
     */
    public function getOnclick($htmlId = null)
    {
        return sprintf(
            'window.prTrackingTestConnection(\'%s\', \'%s\', \'%s\'); return false;',
            $this->getUrl(Config::TEST_CONNECTION_URL, ['carrier' => Config::FEDEX]),
            $htmlId,
            $this->getFieldIds()
        );
    }

    /**
     * @return string
     */
    public function getFieldIds()
    {
        $suffixIds = [
            'key',
            'password',
            'account_number',
            'meter_number',
            'sandbox_mode'
        ];
        $ids = implode("," . self::SERVICE_PREFIX, $suffixIds);

        return self::SERVICE_PREFIX . $ids;
    }
}