<?php

namespace Cminds\Marketplace\Block\Report;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\ResourceModel\Report\Bestsellers\Collection
    as BestsellersCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Bestsellers extends Template
{
    /**
     * Page title.
     *
     * @var string
     */
    public $title = 'Products Bestsellers Report';

    /**
     * Last column in grid.
     *
     * @var string
     */
    private $lastColumnHeader = 'Qty Ordered';

    /**
     * Bestseller collection instance.
     *
     * @var BestsellersCollection
     */
    private $bestsellersCollection;

    /**
     * ResourceConnection instance.
     *
     * @var ResourceConnection
     */
    private $coreResource;

    /**
     * MaretplaceHelper instance.
     *
     * @var MarketplaceHelper
     */
    private $marketplaceHelper;

    /**
     * CurrencyHelper instance.
     *
     * @var CurrencyHelper
     */
    private $currencyHelper;

    /**
     * EntityAttribute resource model instance.
     *
     * @var Attribute
     */
    private $entityAttribute;

    public function __construct(
        Context $context,
        BestsellersCollection $bestsellersCollection,
        ResourceConnection $resourceConnection,
        MarketplaceHelper $marketplaceHelper,
        CurrencyHelper $currencyHelper,
        Attribute $entityAttribute
    ) {
        parent::__construct($context);

        $this->bestsellersCollection = $bestsellersCollection;
        $this->coreResource = $resourceConnection;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->currencyHelper = $currencyHelper;
        $this->entityAttribute = $entityAttribute;
    }

    /**
     * Get bestseller collection with filters.
     *
     * @return BestsellersCollection
     */
    public function getCollection()
    {
        return $this->prepareCollection();
    }

    /**
     * Add filters to bestseller collection.
     *
     * @return BestsellersCollection
     */
    private function prepareCollection()
    {
        $collection = $this->bestsellersCollection;

        if ($this->getFilter('from') && $this->getFilter('to')) {
            $collection->setDateRange(
                $collection->formatDate($this->getFilter('from')),
                $collection->formatDate($this->getFilter('to'))
            );
        }

        if ($this->getFilter('period_type')) {
            $collection->setPeriod($this->getFilter('period_type'));
        } else {
            $collection->setPeriod('day');
        }

        $collection
            ->getSelect()
            ->join(
                ['eav' => $this->getEntityIntTable()],
                'eav.entity_id = product_id',
                []
            );
        $collection
            ->getSelect()
            ->where(
                'eav.value = ?',
                $this->getSupplierId()
            );

        $collection
            ->getSelect()->where(
                'eav.attribute_id = ?',
                $this->getAttributeId()
            );

        $collection->addStoreFilter(0);

        return $collection->load();
    }

    /**
     * Get value from post/get array by key.
     *
     * @param  string $key Key in POST or GET array.
     *
     * @return string
     */
    private function getFilter($key)
    {
        return $this->_request->getParam($key);
    }

    /**
     * Get period string.
     *
     * @param string $dateString Date in string.
     *
     * @return string
     */
    public function getPeriodString($dateString)
    {
        $date = new \DateTime($dateString);

        switch ($this->getFilter('period_type')) {
            case 'day':
                return $date->format('D, F d');
                break;
            case 'month':
                return $date->format('F Y');
                break;
            case 'year':
                return $date->format('Y');
                break;
            default :
                return $date->format('D, F d');
                break;
        }
    }

    /**
     * Page title getter.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get last column in grid.
     *
     * @return string
     */
    public function getLastColumnHeader()
    {
        return $this->lastColumnHeader;
    }

    /**
     * Get table name for products entities.
     *
     * @return string
     */
    private function getEntityIntTable()
    {
        return $this->coreResource->getTableName('catalog_product_entity_int');
    }

    /**
     * Get ID for "creator_id" attribute.
     *
     * @return int
     */
    private function getAttributeId()
    {
        $eavAttributeObject = $this->entityAttribute;

        $eavAttribute = $eavAttributeObject;

        return $eavAttribute->getIdByCode('catalog_product', 'creator_id');
    }

    /**
     * Get logged supplier ID.
     *
     * @return bool|mixed
     */
    private function getSupplierId()
    {
        return $this->marketplaceHelper->getSupplierId();
    }

    /**
     * Get currency helper instance.
     *
     * @return CurrencyHelper
     */
    public function getCurrencyHelper()
    {
        return $this->currencyHelper;
    }
}
