<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\ResourceModel\Fulltext;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\DefaultFilterStrategyApplyChecker;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\DefaultFilterStrategyApplyCheckerInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\TotalRecordsResolverInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Request\EmptyRequestDataException;
use Magento\Framework\Search\Request\NonExistingRequestNameException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\LayeredNavigation\Model\ResourceModel\Search\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Zend_Db_Exception;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{

    /**
     * @var null
     */
    public $collectionClone = null;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_categoryId;
    private $queryText;
    private $order = [];
    private $searchRequestName;
    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;
    /**
     * @var SearchInterface
     */
    private $search;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var SearchResultInterface
     */
    private $searchResult;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var array
     */
    private $searchOrders;
    /**
     * @var DefaultFilterStrategyApplyCheckerInterface
     */
    private $defaultFilterStrategyApplyChecker;
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;
    /**
     * @var TotalRecordsResolverFactory
     */
    private $totalRecordsResolverFactory;

    /**
     * @var SearchResultApplierFactory
     */
    private $searchResultApplierFactory;

    /**
     * @var SearchCriteriaResolverFactory
     */
    private $searchCriteriaResolverFactory;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param AdapterInterface|null $connection
     * @param string $searchRequestName
     * @param SearchResultFactory|null $searchResultFactory
     * @param ProductLimitationFactory|null $productLimitationFactory
     * @param MetadataPool|null $metadataPool
     * @param SearchInterface|null $search
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param FilterBuilder|null $filterBuilder
     * @param SearchCriteriaResolverFactory|null $searchCriteriaResolverFactory
     * @param TotalRecordsResolverFactory|null $totalRecordsResolverFactory
     * @param SearchResultApplierFactory|null $searchResultApplierFactory
     * @param DefaultFilterStrategyApplyCheckerInterface|null $defaultFilterStrategyApplyChecker
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        /** @noinspection PhpUndefinedClassInspection */ LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        TemporaryStorageFactory $temporaryStorageFactory,
        AdapterInterface $connection = null,
        $searchRequestName = 'catalog_view_container',
        SearchResultFactory $searchResultFactory = null,
        ProductLimitationFactory $productLimitationFactory = null,
        MetadataPool $metadataPool = null,
        SearchInterface $search = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterBuilder $filterBuilder = null,
        SearchCriteriaResolverFactory $searchCriteriaResolverFactory = null,
        TotalRecordsResolverFactory $totalRecordsResolverFactory = null,
        SearchResultApplierFactory $searchResultApplierFactory = null,
        DefaultFilterStrategyApplyCheckerInterface $defaultFilterStrategyApplyChecker = null
    ) {

        $this->searchResultFactory = $searchResultFactory ?? ObjectManager::getInstance()
                ->get(SearchResultFactory::class);
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection,
            $productLimitationFactory,
            $metadataPool
        );
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->searchRequestName = $searchRequestName;
        $this->search = $search ?: ObjectManager::getInstance()->get(SearchInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->filterBuilder = $filterBuilder ?: ObjectManager::getInstance()
            ->get(FilterBuilder::class);
        $this->defaultFilterStrategyApplyChecker = $defaultFilterStrategyApplyChecker ?: ObjectManager::getInstance()
            ->get(DefaultFilterStrategyApplyChecker::class);

        $this->searchResultApplierFactory = $searchResultApplierFactory ?: ObjectManager::getInstance()
            ->get(SearchResultApplierFactory::class);
        $this->searchCriteriaResolverFactory = $searchCriteriaResolverFactory ?: ObjectManager::getInstance()
            ->get(SearchCriteriaResolverFactory::class);
        $this->totalRecordsResolverFactory = $totalRecordsResolverFactory ?: ObjectManager::getInstance()
            ->get(TotalRecordsResolverFactory::class);
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function addLayerCategoryFilter($categories)
    {
        $this->addFieldToFilter('category_ids', implode(',', $categories));

        return $this;
    }

    /**
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->searchResult !== null) {
            throw new RuntimeException('Illegal state');
        }

        if (is_array($condition) && $this->_isElasticSearchEngine()) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } elseif (!is_array($condition) || !in_array(key($condition), ['from', 'to'])) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } else {
            if (!empty($condition['from'])) {
                $this->filterBuilder->setField("{$field}.from");
                $this->filterBuilder->setValue($condition['from']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
            if (!empty($condition['to'])) {
                $this->filterBuilder->setField("{$field}.to");
                $this->filterBuilder->setValue($condition['to']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isElasticSearchEngine()
    {
        $currentEngine = $this->_scopeConfig->getValue(
            EngineInterface::CONFIG_ENGINE_PATH,
            ScopeInterface::SCOPE_STORE
        );
        if ($currentEngine == 'elasticsearch' || $currentEngine == 'elasticsearch5') {
            return true;
        }

        return false;
    }

    /**
     * @param $attributeCode
     * @return Collection
     */
    public function removeAttributeSearch($attributeCode)
    {
        if (is_array($attributeCode)) {
            foreach ($attributeCode as $attCode) {
                $this->searchCriteriaBuilder->removeFilter($attCode);
            }
        } else {
            $this->searchCriteriaBuilder->removeFilter($attributeCode);
        }

        $this->_isFiltersRendered = false;

        return $this->loadWithFilter();
    }

    /**
     * @param $attribute
     * @param $condition
     * @param string $joinType
     * @return string
     */
    public function getAttributeConditionSql($attribute, $condition, $joinType = 'inner')
    {
        return $this->_getAttributeConditionSql($attribute, $condition, $joinType);
    }

    /**
     * @return $this
     */
    public function resetTotalRecords()
    {
        $this->_totalRecords = null;

        return $this;
    }

    /**
     * Test search.
     *
     * @param SearchInterface $object
     * @return void
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function setSearch(SearchInterface $object)
    {
        $this->search = $object;
    }

    /**
     * Set filter builder.
     *
     * @param FilterBuilder $object
     * @return void
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function setFilterBuilder(FilterBuilder $object)
    {
        $this->filterBuilder = $object;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    /**
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attr) {
                $this->order[$attr] = $dir;
            }
        } else {
            $this->order[$attribute] = $dir;
        }

        $this->setSearchOrder($attribute, $dir);
        if ($this->defaultFilterStrategyApplyChecker->isApplicable()) {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * Set sort order for search query.
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    private function setSearchOrder($field, $direction)
    {
        $field = (string)$this->_getMappedField($field);
        $direction = strtoupper($direction) == self::SORT_ORDER_ASC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;

        $this->searchOrders[$field] = $direction;
    }

    /**
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();
        $result = [];
        $aggregations = $this->searchResult->getAggregations();
        // This behavior is for case with empty object when we got EmptyRequestDataException
        if (null !== $aggregations) {
            $bucket = $aggregations->getBucket($field . RequestGenerator::BUCKET_SUFFIX);
            if ($bucket) {
                foreach ($bucket->getValues() as $value) {
                    $metrics = $value->getMetrics();
                    $result[$metrics['value']] = $metrics;
                }
            } else {
                throw new StateException(__("The bucket doesn't exist."));
            }
        }
        return $result;
    }

    /**
     * Render filters.
     *
     * @return $this
     */
    protected function _renderFilters()
    {
        $this->_filters = [];
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        file_put_contents(BP . '/var/log/ox.log', ob_get_clean() . "\n", FILE_APPEND);
        return parent::_renderFilters();
    }

    /**
     * Specify category filter for product collection
     *
     * @param Category $category
     * @return $this
     */
    public function addCategoryFilter(Category $category)
    {
        $this->addFieldToFilter('category_ids', $category->getId());
        /**
         * This changes need in backward compatible reasons for support dynamic improved algorithm
         * for price aggregation process.
         */
        if ($this->defaultFilterStrategyApplyChecker->isApplicable()) {
            parent::addCategoryFilter($category);
        } else {
            $this->_productLimitationPrice();
        }

        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        /**
         * This changes need in backward compatible reasons for support dynamic improved algorithm
         * for price aggregation process.
         */
        if ($this->defaultFilterStrategyApplyChecker->isApplicable()) {
            parent::setVisibility($visibility);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @since 101.0.4
     */
    public function clear()
    {
        $this->searchResult = null;
        return parent::clear();
    }

    /**
     * @return Collection|null
     */
    public function getCollectionClone()
    {
        if ($this->collectionClone === null) {
            $this->collectionClone = clone $this;
            $this->collectionClone->setSearchCriteriaBuilder($this->searchCriteriaBuilder->cloneObject());
        }

        $searchCriterialBuilder = $this->collectionClone->getSearchCriteriaBuilder()->cloneObject();

        $collectionClone = clone $this->collectionClone;
        $collectionClone->setSearchCriteriaBuilder($searchCriterialBuilder);

        return $collectionClone;
    }

    /**
     * @param SearchCriteriaBuilder $object
     */
    public function setSearchCriteriaBuilder(SearchCriteriaBuilder $object)
    {
        $this->searchCriteriaBuilder = $object;
    }

    /**
     * @throws LocalizedException
     * @throws Zend_Db_Exception
     */
    protected function _renderFiltersBefore()
    {
        if ($this->isLoaded()) {
            return;
        }

        if ($this->searchRequestName !== 'quick_search_container'
            || strlen(trim($this->queryText))
        ) {
            $this->prepareSearchTermFilter();
            $this->preparePriceAggregation();

            $searchCriteria = $this->getSearchCriteriaResolver()->resolve();
            try {
                $this->searchResult = $this->getSearch()->search($searchCriteria);
                $this->_totalRecords = $this->getTotalRecordsResolver($this->searchResult)->resolve();
            } catch (EmptyRequestDataException $e) {
                $this->searchResult = $this->createEmptyResult();
            } catch (NonExistingRequestNameException $e) {
                $this->_logger->error($e->getMessage());
                throw new LocalizedException(__('An error occurred. For details, see the error log.'));
            }
        } else {
            $this->searchResult = $this->createEmptyResult();
        }

        $this->getSearchResultApplier($this->searchResult)->apply();
        parent::_renderFiltersBefore();
    }

    /**
     * Prepare search term filter for text query.
     *
     * @return void
     */
    private function prepareSearchTermFilter(): void
    {
        if ($this->queryText) {
            $this->filterBuilder->setField('search_term');
            $this->filterBuilder->setValue($this->queryText);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }
    }

    /**
     * Prepare price aggregation algorithm.
     *
     * @return void
     */
    private function preparePriceAggregation(): void
    {
        $priceRangeCalculation = $this->_scopeConfig->getValue(
            AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->filterBuilder->setField('price_dynamic_algorithm');
            $this->filterBuilder->setValue($priceRangeCalculation);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }
    }

    /**
     * Get search criteria resolver.
     *
     * @return SearchCriteriaResolverInterface
     */
    private function getSearchCriteriaResolver(): SearchCriteriaResolverInterface
    {
        return $this->searchCriteriaResolverFactory->create(
            [
                'builder' => $this->getSearchCriteriaBuilder(),
                'collection' => $this,
                'searchRequestName' => $this->searchRequestName,
                'currentPage' => (int)$this->_curPage,
                'size' => $this->getPageSize(),
                'orders' => $this->searchOrders,
            ]
        );
    }

    /**
     * Set search criteria builder.
     *
     * @return SearchCriteriaBuilder
     * @deprecated 100.1.0
     */
    private function getSearchCriteriaBuilder()
    {
        if ($this->searchCriteriaBuilder === null) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

    /**
     * Get search.
     *
     * @return SearchInterface
     * @deprecated 100.1.0
     */
    private function getSearch()
    {
        if ($this->search === null) {
            $this->search = ObjectManager::getInstance()->get(SearchInterface::class);
        }
        return $this->search;
    }

    /**
     * Get total records resolver.
     *
     * @param SearchResultInterface $searchResult
     * @return TotalRecordsResolverInterface
     */
    private function getTotalRecordsResolver(SearchResultInterface $searchResult): TotalRecordsResolverInterface
    {
        return $this->totalRecordsResolverFactory->create(
            [
                'searchResult' => $searchResult,
            ]
        );
    }

    /**
     * Create empty search result
     *
     * @return SearchResultInterface
     */
    private function createEmptyResult()
    {
        return $this->searchResultFactory->create()->setItems([]);
    }

    /**
     * Get search result applier.
     *
     * @param SearchResultInterface $searchResult
     * @return SearchResultApplierInterface
     */
    private function getSearchResultApplier(SearchResultInterface $searchResult): SearchResultApplierInterface
    {
        return $this->searchResultApplierFactory->create(
            [
                'collection' => $this,
                'searchResult' => $searchResult,
                /** This variable sets by serOrder method, but doesn't have a getter method. */
                'orders' => $this->_orders,
                'size' => $this->getPageSize(),
                'currentPage' => (int)$this->_curPage,
            ]
        );
    }

    /**
     * Get filter builder.
     *
     * @return FilterBuilder
     * @deprecated 100.1.0
     */
    private function getFilterBuilder()
    {
        if ($this->filterBuilder === null) {
            $this->filterBuilder = ObjectManager::getInstance()->get(FilterBuilder::class);
        }
        return $this->filterBuilder;
    }

}
