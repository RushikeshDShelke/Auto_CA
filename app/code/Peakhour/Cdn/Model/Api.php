<?php

namespace Peakhour\Cdn\Model;

use Peakhour\Cdn\Helper\Data;


class Api
{

    const FLUSH_ALL = 'all';
    const FLUSH_CDN = 'cdn';
    const FLUSH_PAGESPEED = 'pagespeed';

    /**
     * @var Config $config,
     */
    private $config;


    /**
     * @var Data
     */
    private $helper;

    /**
     * Api constructor.
     * @param Config $config
     * @param Data $helper
     */
    public function __construct(
        Config $config,
        Data $helper
    ) {
        $this->config = $config;
        $this->helper = $helper;
    }


    function makeRequestBody($urls, $inverse=false, $flushType=self::FLUSH_ALL, $purgeAll=false)
    {
        $paths = array();

        foreach ($urls as $url) {
            $parsed = parse_url( $url );
            $path = ( isset( $parsed['path'] ) ? $parsed['path'] : null );
            if ( empty( $path ) ) {
                $path = '/';
            }
            if ( isset( $parsed['query'] ) ) {
                $path = $path . '?' . $parsed['query'];
            }
            array_push($paths, $path );

        }
        $paths = array_values(array_unique($paths));

        return json_encode(array('paths' => $paths, 'inverse' => $inverse, 'flush_type' => $flushType, 'purge_all' => $purgeAll));
    }

    function doRequest($url, $method, $body=null, $apiKey=null)
    {
        if (is_null($apiKey)) {
            $apiKey = $this->config->getApiKey();
        }

        $headers = array(
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        );

        $this->helper->debug($method . ' ' . $url . ' ' . $body . '' . $apiKey);

        try {

            $client = new \Zend_Http_Client();

            $client->setUri($url);
            $client->setHeaders($headers);
            if (!is_null($body)) {
                $client->setRawData($body);
            }

            $response = $client->request($method);


            if ($response->isError()) {
                throw new \Exception('Error in response (' . $response->getStatus() . ') ' . $response->getBody());
            }
            $this->helper->debug("returning");
            return array('success' => true, 'error' => null, 'body' => $response->getBody());
        } catch (\Exception $e) {
            $this->helper->debug("Failed $method to $url: " . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    function getResourcesUrl($domain=null)
    {
        if (is_null($domain)) {
            $domain = $this->config->getDomain();
        }
        return $this->config->getApiUrl()    . 'domains/' . $domain . '/services/rp/cdn/resources';
    }

    function getTagsUrl($domain=null)
    {
        if (is_null($domain)) {
            $domain = $this->config->getDomain();
        }
        return $this->config->getApiUrl() . 'domains/' . $domain . '/services/rp/cdn/tag';
    }

    function doResourcesRequest($method, $urls)
    {
        $resourcesUrl = $this->getResourcesUrl();
        $body = $this->makeRequestBody($urls);
        $this->doRequest($resourcesUrl, $method, $body);
    }

    public function cacheResource($url)
    {
        $this->cacheResources(array($url));
    }

    public function cacheResources($urls)
    {
            $this->doResourcesRequest('POST', $urls);
    }

    public function purgeAll($flushType = self::FLUSH_ALL)
    {
        $resourcesUrl = $this->getResourcesUrl();
        $body = $this->makeRequestBody([], false, $flushType, true);
        return $this->doRequest($resourcesUrl, 'DELETE', $body);
    }

    public function purgeWildcard($url)
    {
        $resourcesUrl = $this->getResourcesUrl();
        $body = $this->makeRequestBody((array)$url);
        return $this->doRequest($resourcesUrl, 'DELETE', $body);
    }

    public function purgeResource($url)
    {
        $this->purgeResources(array($url));
    }

    public function purgeResources($urls)
    {
        $this->doResourcesRequest('DELETE', $urls);
    }

    public function purgeTags($tags)
    {
        $this->doRequest($this->getTagsUrl(),'DELETE', json_encode(array('tags' => $tags)));
    }


    public function testConnection($apiKey, $domain)
    {
        $resourcesUrl = $this->getResourcesUrl($domain);
        return $this->doRequest($resourcesUrl, 'OPTIONS', null, $apiKey);
    }
}