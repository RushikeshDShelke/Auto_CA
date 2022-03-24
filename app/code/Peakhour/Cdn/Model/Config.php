<?php

namespace Peakhour\Cdn\Model;


class Config extends \Magento\PageCache\Model\Config
{
    /**
     * Cache types
     */
    const PEAKHOUR = 'peakhour';

    /**
     * XML path to API Key
     */
    const XML_PEAKHOUR_API_KEY = 'system/full_page_cache/peakhour/peakhour_api_key';

    /**
     * XML path to API domain
     */
    const XML_PEAKHOUR_DOMAIN = 'system/full_page_cache/peakhour/peakhour_domain';

    /**
     * XML path to API url
     */
    const XML_PEAKHOUR_API_URL = 'system/full_page_cache/peakhour/peakhour_api_url';

    /**
     * XML path to enable debug
     */
    const XML_PEAKHOUR_DEBUG = 'system/full_page_cache/peakhour/peakhour_enable_debug';

    /**
     * Is Peakhour enabled?
     *
     * @return bool
     */
    public function isPeakhourEnabled()
    {
        if ($this->getType() == Config::PEAKHOUR) {
            return true;
        }

        return false;
    }

    /**
     * Return Peakhour API token
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(self::XML_PEAKHOUR_API_KEY);
    }

    /**
     * Return Peakhour API Url
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->_scopeConfig->getValue(self::XML_PEAKHOUR_API_URL);
    }

    /**
     * Return Peakhour  domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->_scopeConfig->getValue(self::XML_PEAKHOUR_DOMAIN);
    }

    /**
     * Return Peakhour debug enabled
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PEAKHOUR_DEBUG);
    }
}