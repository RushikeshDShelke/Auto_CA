<?php
/*


*/

namespace Dotzotfront\Base\Helper;

class Data extends Main
{
    /**
     * @var string
     */
    protected $_configSectionId = 'plumbase';

    /**
     * Receive true if Dotzotfront module is enabled
     *
     * @param  string $store
     * @return bool
     */
    public function moduleEnabled($store = null)
    {
        return true;
    }

    /**
     * Receive true admin notifications is enabled
     *
     * @return bool
     */
    public function isAdminNotificationEnabled()
    {
        $m = 'Mage_Admin'.'Not'.'ification';
        return !$this->scopeConfig->isSetFlag(
            $this->_getAd() . '/' . $m,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Receive config path
     *
     * @return string
     */
    protected function _getAd()
    {
        return 'adva'.'nced/modu'.
            'les_dis'.'able_out'.'put';
    }
}
