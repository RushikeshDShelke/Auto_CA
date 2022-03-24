<?php

namespace Cminds\Marketplace\Block\Report;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Reports\Model\ResourceModel\Product\Lowstock\Collection as LowStockCollection;

class Lowstock extends Template
{
    private $title = 'Low Stock';

    private $marketplaceHelper;
    private $directoryList;
    private $lowStockCollection;

    private $columns = ['Product Name', 'Product SKU', 'Stock Qty'];
    private $removeIndexes = ['entity_id'];
    private $availableIndexes = ['name', 'sku', 'qty'];

    public function __construct(
        Context $context,
        MarketplaceHelper $marketplaceHelper,
        DirectoryList $directoryList,
        LowStockCollection $lowStockCollection
    ) {
        parent::__construct($context);

        $this->marketplaceHelper = $marketplaceHelper;
        $this->directoryList = $directoryList;
        $this->lowStockCollection = $lowStockCollection;
    }

    public function getCollection()
    {
        return $this->_prepareCollection();
    }

    protected function _prepareCollection() // @codingStandardsIgnoreLine
    {
        $collection = $this->lowStockCollection
            ->addAttributeToSelect('*')
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter(1)
            ->useNotifyStockQtyFilter(1)
            ->addAttributeToFilter('creator_id', $this->getSupplierId())
            ->setOrder(
                'qty',
                \Magento\Framework\DB\MapperInterface::SORT_ORDER_ASC
            );

        return $collection->load();
    }

    public function getTitle()
    {
        return $this->title;
    }

    private function getSupplierId()
    {
        return $this->marketplaceHelper->getSupplierId();
    }

    public function getCsvFileEnhanced()
    {
        $this->_isExport = true;

        $io = new \Magento\Framework\Filesystem\Io\File();

        $path = $this->directoryList->getPath('var');
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

        $st = fopen($file, 'w');
        if ($this->columns) {
            fputcsv($st, $this->columns);
        }

        $collection = $this->getCollection();

        foreach ($collection as $item) {
            $i = $item->getData();

            if (!$this->availableIndexes) {
                if ($this->removeIndexes && is_array($this->removeIndexes)) {
                    foreach ($this->removeIndexes as $index) {
                        unset($i[$index]);
                    }
                }

                foreach ($i as $k => $v) {
                    if (is_object($v)) {
                        unset($i[$k]);
                    }
                }
                fputcsv($st, $i);
            } else {
                $d = [];
                foreach ($this->availableIndexes as $k) {
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
}
