<?php

namespace Cminds\Marketplace\Block\Report;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\ResourceModel\Report\Viewed\Collection as MostviewedCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\View\Element\Template\Context;

class Mostviewed extends \Magento\Framework\View\Element\Template
{
    protected $_columns = [
        'Period',
        'Qty Ordered',
        'Product ID',
        'Products Name',
        'Price',
    ];
    protected $_removeIndexes = ['value'];
    public $title = 'Most Viewed Products Report';
    protected $_lastColumnHeader = 'Number of Views';
    protected $_availableIndexes = false;

    protected $_mostviewedCollection;
    protected $_coreResource;
    protected $_objectManager;
    protected $_marketplaceHelper;
    protected $_directoryList;
    protected $_currencyHelper;

    public function __construct(
        Context $context,
        MostviewedCollection $mostviewedCollection,
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManagerInterface,
        MarketplaceHelper $marketplaceHelper,
        DirectoryList $directoryList,
        CurrencyHelper $currencyHelper
    ) {
        parent::__construct($context);

        $this->_mostviewedCollection = $mostviewedCollection;
        $this->_coreResource = $resourceConnection;
        $this->_objectManager = $objectManagerInterface;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_directoryList = $directoryList;
        $this->_currencyHelper = $currencyHelper;
    }

    public function getCollection()
    {
        return $this->_prepareCollection();
    }

    protected function _prepareCollection()
    {
        $collection = $this->_mostviewedCollection;
        $store = $this->_storeManager->getStore()->getId();

        if ($this->getFilter('from') && $this->getFilter('to')) {
            $collection->setDateRange(
                $collection->formatDate($this->getFilter('from')),
                $collection->formatDate($this->getFilter('to'))
            );
        }

        if ($this->getFilter('period_type')) {
            $collection->setPeriod($this->getFilter('period_type'));
        }
        $collection->addStoreFilter($store);
        $collection->getSelect()->join(
            ['eav' => $this->_getEntityIntTable()],
            'eav.entity_id = product_id',
            []
        );
        $collection->getSelect()->where(
            'eav.value = ?',
            $this->_getSupplierId()
        );
        
        return $collection->load();
    }

    protected function getFilter($key)
    {
        return $this->_request->getParam($key);
    }

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
                return $date->format('m/d/Y');
                break;
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getLastColumnHeader()
    {
        return $this->_lastColumnHeader;
    }

    protected function _getEntityIntTable()
    {
        return $this->_coreResource->getTableName('catalog_product_entity_int');
    }

    protected function _getAttributeId()
    {
        $eavAttributeObject = $this->_objectManager
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');

        $eavAttribute = $eavAttributeObject;

        return $eavAttribute->getIdByCode('catalog_product', 'creator_id');
    }

    protected function _getSupplierId()
    {
        return $this->_marketplaceHelper->getSupplierId();
    }

    public function getCsvFileEnhanced()
    {
        $this->_isExport = true;

        $io = new \Magento\Framework\Filesystem\Io\File();

        $path = $this->_directoryList->getPath('var');
        $name = md5(microtime());
        $file = $path . '/' . $name . '.csv';

        while (file_exists($file)) {
            sleep(1);
            $name = md5(microtime());
            $file = $path . '/' . $name . '.csv';
        }

        $io->setAllowCreateFolders(true);
        $io->open(['path' => $path]);
        $io->write($file, '', 'w+');
        $io->streamLock(true);

        $st = fopen($file, "w");
        if ($this->_columns) {
            fputcsv($st, $this->_columns);
        }
        foreach ($this->getCollection() AS $item) {
            $i = $item->getData();
            if (!$this->_availableIndexes) {
                if ($this->_removeIndexes && is_array($this->_removeIndexes)) {
                    foreach ($this->_removeIndexes AS $index) {
                        unset($i[$index]);
                    }
                }

                foreach ($i AS $k => $v) {
                    if (is_object($v)) {
                        unset($i[$k]);
                    }
                }
                fputcsv($st, $i);
            } else {
                $d = [];
                foreach ($this->_availableIndexes AS $k) {
                    $d[] = $i[$k];
                }
                fputcsv($st, $d);
            }
        }

        $io->streamUnlock();
        $io->streamClose();

        return [
            'type' => 'filename',
            'value' =>  'var/' . $name . '.csv',
            'rm' => true,
        ];
    }

    public function getParam($key, $defaultValue = null)
    {
        return $this->_request->getParam($key, $defaultValue);
    }

    public function getCurrencyHelper()
    {
        return $this->_currencyHelper;
    }
}
