<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogSearch\Model\Layer\Filter\Price as FilterPrice;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\LayeredNavigation\Helper\Helper as Helper;
use Zend_Db_Select;

class Price extends FilterPrice
{

    protected $_helper;
    protected $_filterVal = null;
    protected $json;
    private $dataProvider;
    private $priceCurrency;
    /**
     * @var string
     */
    private $currencySymbol;

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        Session $customerSession,
        Algorithm $priceAlgorithm,
        PriceCurrencyInterface $priceCurrency,
        AlgorithmFactory $algorithmFactory,
        PriceFactory $dataProviderFactory,
        Helper $helper,
        array $data = [],
        Json $json = null
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );

        $this->priceCurrency = $priceCurrency;
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->_helper = $helper;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
    }

    public function apply(RequestInterface $request)
    {
        if (!$this->pluginEnable()) {
            return parent::apply($request);
        }

        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }
        if (is_string($filter)) {
            $filter = explode(',', $filter);
        }
        $filterParams = $filter;
        $filter = $this->dataProvider->validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        $this->dataProvider->setInterval($filter);
        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        list($from, $to) = $this->_filterVal = $filter;

        $this->getLayer()->getProductCollection()->addFieldToFilter(
            'price',
            [
                'from' => $from,
                'to' => $to
            ]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }

    public function pluginEnable()
    {
        return $this->_helper->isEnabled();
    }
    public function getIsSlider() {
        return $this->pluginEnable() && $this->_helper->isPriceSlider();
    }

    protected function _renderRangeLabel($fromPrice, $toPrice)
    {
        if (!$this->pluginEnable()) {
            return parent::_renderRangeLabel($fromPrice, $toPrice);
        }
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        } else {
            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * @return bool|false|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSliderConfig()
    {
        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $format = explode('%s', $this->getOutputFormat());
        if (2 != count($format)) {
            $format = [
                $this->getCurrencySymbol(),
                ''
            ];
        }
        $config = [
            "min" => $productCollection->getMinPrice(),
            "max" => $productCollection->getMaxPrice(),
            "step" => 1,
            "formatMoney" => [
                "prefix" => $format[0] ?: '',
                "suffix" => $format[1] ?: '',
            ],
        ];

        return $this->json->serialize($config);
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrencySymbol()
    {
        if (empty($this->currencySymbol)) {
            $this->currencySymbol = $this->getCurrentCurrency()->getCurrencySymbol();
        }

        return $this->currencySymbol;
    }

    /**
     * @return Currency
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCurrency()
    {
        /** @var Store $store */
        $store = $this->_storeManager->getStore();
        /** @var Currency $currentCurrency */
        $currentCurrency = $store->getCurrentCurrency();
        return $currentCurrency;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOutputFormat()
    {
        return $this->getCurrentCurrency()->getOutputFormat();
    }

    protected function _getItemsData()
    {
        if (!$this->pluginEnable()) {
            return parent::_getItemsData();
        }

        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_filterVal && method_exists($productCollection, 'getCollectionClone')) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch(['price.from', 'price.to']);
        }

        $facets = $productCollection->getFacetedData($attribute->getAttributeCode());

        $data = [];
        if (count($facets) > 1) {
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count);
            }
        }

        return $data;
    }

    private function prepareData($key, $count)
    {
        list($from, $to) = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        if ($to == '*') {
            $to = $this->getTo($to);
        }
        $label = $this->_renderRangeLabel(
            empty($from) ? 0 : $from * $this->getCurrencyRate(), empty($to) ? $to : $to * $this->getCurrencyRate()
        );
        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }

}
