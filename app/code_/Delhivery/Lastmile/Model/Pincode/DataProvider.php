<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Model\Pincode;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Loaded data cache
     * 
     * @var array
     */
    protected $loadedData;

    /**
     * Data persistor
     * 
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * constructor
     * 
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Delhivery\Lastmile\Model\ResourceModel\Pincode\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Delhivery\Lastmile\Model\ResourceModel\Pincode\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var \Delhivery\Lastmile\Model\Pincode $pincode */
        foreach ($items as $pincode) {
            $this->loadedData[$pincode->getId()] = $pincode->getData();

        }
        $data = $this->dataPersistor->get('delhivery_lastmile_pincode');
        if (!empty($data)) {
            $pincode = $this->collection->getNewEmptyItem();
            $pincode->setData($data);
            $this->loadedData[$pincode->getId()] = $pincode->getData();
            $this->dataPersistor->clear('delhivery_lastmile_pincode');
        }
        return $this->loadedData;
    }
}
