<?php

namespace Cminds\Marketplace\Controller\Reports;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data;
use Cminds\Marketplace\Model\ResourceModel\Report\Bestsellers\Collection
    as BestsellersCollection;
use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Exportbestsellers extends AbstractController
{
    /**
     * Marketplace helper instance.
     *
     * @var Data
     */
    private $marketplaceHelper;

    /**
     * ResourceConnection instance.
     *
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Raw factory instance.
     *
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * Bestseller collection instance.
     *
     * @var BestsellersCollection
     */
    private $bestsellersCollection;

    /**
     * EntityAttribute resource model instance.
     *
     * @var Attribute
     */
    private $entityAttribute;

    public function __construct(
        Context $context,
        Helper $helper,
        Data $cmindsHelper,
        ResourceConnection $resourceConnection,
        RawFactory $rawFactory,
        BestsellersCollection $bestsellersCollection,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Attribute $entityAttribute
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->marketplaceHelper = $cmindsHelper;
        $this->resourceConnection = $resourceConnection;
        $this->resultRawFactory = $rawFactory;
        $this->bestsellersCollection = $bestsellersCollection;
        $this->entityAttribute = $entityAttribute;
    }

    /**
     * Prepare CSV file with bestsellers.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $collection = $this->getCollection();
        $productCsv = [];

        foreach ($collection as $item) {
            $productCsv[] = [
                'Period' => $item['period'],
                'Qty Ordered' => $item['qty_ordered'],
                'Product ID' => $item['product_id'],
                'Products Name' => $item['product_name'],
                'Price' => $item['product_price'],
            ];
        }

        $this->marketplaceHelper
            ->prepareCsvHeaders('bestsellet_export_' . date('Y-m-d') . '.csv');

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->marketplaceHelper->array2Csv($productCsv));

        return $resultRaw;
    }

    /**
     * Get bestseller collection with filters.
     *
     * @return BestsellersCollection
     */
    private function getCollection()
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
     * @param $key
     *
     * @return mixed
     */
    private function getFilter($key)
    {
        return $this->_request->getParam($key);
    }

    /**
     * Get table name for products entities.
     *
     * @return string
     */
    private function getEntityIntTable()
    {
        return $this->resourceConnection
            ->getTableName('catalog_product_entity_int');
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
}
