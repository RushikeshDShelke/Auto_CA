<?php

namespace Cminds\Marketplace\Block\Report;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;

class Order extends Extended
{
    protected $_objectManager;
    protected $_marketplaceHelper;
    protected $_coreResource;
    protected $_salesOrder;
    protected $_dir;
    protected $_currencyHelper;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        MarketplaceHelper $marketplaceHelper,
        SalesOrder $salesOrder,
        DirectoryList $directoryList,
        CurrencyHelper $coreHelper
    ) {
        parent::__construct(
            $context,
            $backendHelper
        );

        $this->_objectManager = $objectManager;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_coreResource = $resourceConnection;
        $this->_salesOrder = $salesOrder;
        $this->_dir = $directoryList;
        $this->_currencyHelper = $coreHelper;
    }

    private $_errors = [];

    protected $_columns = [
        'created_at',
        'sold_count',
        'sum_price',
        'vendor_income',
    ];

    public function getCollection()
    {
        return $this->_prepareCollection();
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('report_orders');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        if ($this->_collection !== null) {
            return $this->_collection;
        }

        $supplier_id = $this->_marketplaceHelper->getSupplierId();

        $eavAttributeObject = $this->_objectManager
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');

        $eavAttribute = $eavAttributeObject;

        $code = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $intEntityName = $this->_coreResource
            ->getTableName('catalog_product_entity_int');
        $orderItemTable = $this->_coreResource
            ->getTableName('sales_order_item');

        $collection = $this->_salesOrder->getCollection();
        $collection->getSelect()
            ->distinct()
            ->join(
                ['i' => $orderItemTable],
                'i.order_id = main_table.entity_id',
                [
                    'SUM(qty_ordered) AS sold_count',
                    'SUM(i.row_total) AS sum_price',
                    'SUM(row_total-(row_total*((i.vendor_fee)/100))) '
                    . 'AS vendor_income',
                    'product_id',
                    'SUM((row_total-i.discount_amount)-((row_total-'
                    . 'i.discount_amount)*(i.vendor_fee/100))) '
                    . 'AS vendor_income_with_discount',
                    'SUM(i.discount_amount) AS sum_discount',
                ]
            )
            ->join(
                ['e' => $intEntityName],
                'e.entity_id = i.product_id AND e.attribute_id = ' . $code,
                []
            )
            ->where('e.value = ?', $supplier_id);

        if ($this->getFilter('from') && strtotime($this->getFilter('from'))) {
            $datetime = new \DateTime($this->getFilter('from'));
            $collection->getSelect()->where(
                'main_table.created_at >= ?',
                $datetime->format('Y-m-d') . " 00:00:00"
            );
        }
        if ($this->getFilter('to') && strtotime($this->getFilter('to'))) {
            $datetime = new \DateTime($this->getFilter('to'));
            $collection->getSelect()->where(
                'main_table.created_at <= ?',
                $datetime->format('Y-m-d') . " 23:59:59"
            );
        }

        switch ($this->getFilter('period_type')) {
            case 'day':
                $collection->getSelect()->group('DAY(main_table.created_at)');
                break;
            case 'month':
                $collection->getSelect()->group('MONTH(main_table.created_at)');
                break;
            case 'year':
                $collection->getSelect()->group('YEAR(main_table.created_at)');
                break;
            default :
                $collection->getSelect()->group('DAY(main_table.created_at)');
                break;
        }

        return $this->_collection = $collection;
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

    public function getCsvFileEnhanced()
    {
        $collectionData = $this->getCollection()->getData();

        $scopeConfig = $this->_objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $value = $isDiscountEff = $scopeConfig
            ->getValue('configuration_marketplace/configure/is_discount_effective');
        if ($value) {
            $this->_columns = [
                'created_at',
                'sold_count',
                'sum_price',
                'vendor_income',
                'sum_discount',
                'vendor_income_with_discount',
            ];
        }

        $this->_isExport = true;

        $io = new \Magento\Framework\Filesystem\Io\File();

        $path = $this->_dir->getPath('var');
        $name = 'orders-' . gmdate('YmdHis');
        $file = $path . '/' . $name . '.csv';

        while (file_exists($file)) {
            sleep(1);
            $name = md5(microtime());
            $file = $path . '/' . $name . '.csv';
        }
       /* $io->setAllowCreateFolders(true);
        $io->open(['path' => $path]);
        $io->write($file, '', 'w+');
        $io->streamLock(true);*/
        $st = fopen($file, "w");
        if ($this->_columns) {
            fputcsv($st, $this->_columns);
        }

        foreach ($collectionData AS $item) {
            $a = [];
            foreach ($this->_columns AS $column) {
                $a[] = $item[$column];
            }

            fputcsv($st, $a);
        }

        $io->streamUnlock();
        $io->streamClose();

        return [
            'type' => 'filename',
            'value' => 'var/' . $name . '.csv',
            'rm' => true,
        ];
    }

    public function isDiscountEffective()
    {
        $scopeConfig = $this->_objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $isDiscountEff = $scopeConfig
            ->getValue('configuration_marketplace/configure/is_discount_effective');

        return $isDiscountEff;
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
