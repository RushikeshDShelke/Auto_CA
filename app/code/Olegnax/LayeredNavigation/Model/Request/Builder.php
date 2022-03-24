<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Request;


use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Binder;
use Magento\Framework\Search\Request\Cleaner;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\RequestInterface;
use ReflectionClass;

class Builder extends \Magento\Framework\Search\Request\Builder
{
    /**
     * @var Http
     */
    protected $requestHttp;

    /**
     * @var array
     */
    protected $dataPlaceholders = [];

    /**
     * @var int
     */
    protected $baseCategory;

    /**
     * @var array
     */
    private $aggregationsOnly = [];

    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        Binder $binder,
        Cleaner $cleaner,
        Http $requestHttp
    ) {
        $this->requestHttp = $requestHttp;
        parent::__construct($objectManager, $config, $binder, $cleaner);
    }

    /**
     * @param string $placeholder
     * @param mixed $value
     * @return Builder
     */
    public function bind($placeholder, $value)
    {
        $this->dataPlaceholders[$placeholder] = $placeholder == 'category_ids'
            ? $this->getPlaceholderCategory($placeholder, $value)
            : $value;

        return $this;
    }

    /**
     * @param $placeholder
     * @param $value
     * @return array
     */
    public function getPlaceholderCategory($placeholder, $value)
    {
        if (!$this->baseCategory) {
            $this->baseCategory = $this->requestHttp->getParam('id') ?: $value;
        }

        if (isset($this->dataPlaceholders[$placeholder])
            && $this->dataPlaceholders[$placeholder] !== $value) {
            $value = $this->getPlaceholderCategoryList($this->dataPlaceholders[$placeholder], $value);
        }

        return $value;
    }

    /**
     * @param $placeholders
     * @param $value
     * @return array
     */
    public function getPlaceholderCategoryList($placeholders, $value)
    {
        $placeholders = array_merge((array)$placeholders, (array)$value);
        $placeholders = array_unique($placeholders);
        $placeholders = array_diff($placeholders, (array)$this->baseCategory);
        $placeholders = array_values($placeholders);

        return $placeholders;
    }

    /**
     * @return RequestInterface
     * @throws \ReflectionException
     */
    public function create()
    {
        $this->applyPlaceholders();
        $request = parent::create();
        $this->removeAggregations($request);
        return $request;
    }

    /**
     * @return $this
     */
    protected function applyPlaceholders()
    {
        foreach ($this->dataPlaceholders as $key => $value) {
            parent::bind($key, $value);
        }

        return $this;
    }

    /**
     * @param $request
     * @return $this
     * @throws \ReflectionException
     */
    private function removeAggregations($request)
    {
        if (!empty($this->aggregationsOnly)) {
            $buckets = $request->getAggregation();
            foreach ($buckets as $key => $bucket) {
                if (!in_array($bucket->getField(), $this->aggregationsOnly)) {
                    unset($buckets[$key]);
                }
            }

            $reflection = new ReflectionClass($request);
            $bucketProperty = $reflection->getProperty('buckets');
            $bucketProperty->setAccessible(true);
            $bucketProperty->setValue($request, $buckets);
        }

        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setAggregationsOnly($code)
    {
        if (!is_array($code)) {
            $code = [$code];
        }
        $this->aggregationsOnly = $code;

        return $this;
    }

    /**
     * @param $placeholder
     * @return $this
     */
    public function removePlaceholder($placeholder)
    {
        if (array_key_exists($placeholder, $this->dataPlaceholders)) {
            unset($this->dataPlaceholders[$placeholder]);
        }
        return $this;
    }

    /**
     * @param $placeholder
     * @return bool
     */
    public function hasPlaceholder($placeholder)
    {
        return array_key_exists($placeholder, $this->dataPlaceholders);
    }

}
