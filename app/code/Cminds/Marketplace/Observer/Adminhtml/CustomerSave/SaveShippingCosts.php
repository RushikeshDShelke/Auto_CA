<?php

namespace Cminds\Marketplace\Observer\Adminhtml\CustomerSave;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\MethodsFactory as MarketplaceMethodsFactory;
use Cminds\Marketplace\Model\ResourceModel\Methods\CollectionFactory as MethodsCollectionFactory;
use Cminds\Marketplace\Model\RatesFactory as MarketplaceRatesFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;
/**
 * Class SaveShippingCosts
 *
 * @package Cminds\Marketplace\Observer\Adminhtml\CustomerSave
 */
class SaveShippingCosts extends CustomerSaveAbstract
{
    const FLAT_RATE = 1;
    const TABLE_RATE = 2;
    const FREE_SHIPPING = 3;

    protected $messageManager;

    /**
     * @var MarketplaceMethodsFactory
     */
    protected $marketplaceMethodsFactory;

    /**
     * @var
     */
    protected $marketplaceHelper;

    /**
     * @var MarketplaceRates
     */
    protected $marketplaceRatesFactory;

    /**
     * @var
     */
    protected $context;

    /**
     * @var MethodsCollectionFactory
     */
    private $methodsCollectionFactory;

    /**
     * Uploader Factory.
     *
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * SaveShippingCosts constructor.
     *
     * @param MarketplaceMethodsFactory $marketplaceMethodsFactory
     * @param MarketplaceRatesFactory $marketplaceRatesFactory
     * @param MarketplaceHelper $marketplaceHelper
     * @param Context $context
     * @param MethodsCollectionFactory $methodsCollectionFactory
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        MessageManager $messageManager,
        MarketplaceMethodsFactory $marketplaceMethodsFactory,
        MarketplaceRatesFactory $marketplaceRatesFactory,
        MarketplaceHelper $marketplaceHelper,
        Context $context,
        MethodsCollectionFactory $methodsCollectionFactory,
        UploaderFactory $uploaderFactory
    ) {
        parent::__construct($marketplaceHelper, $context);

        $this->messageManager = $messageManager;
        $this->marketplaceMethodsFactory = $marketplaceMethodsFactory;
        $this->marketplaceRatesFactory = $marketplaceRatesFactory;
        $this->methodsCollectionFactory = $methodsCollectionFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        $postData = $this->request->getParams();
        
        if(!isset($postData['method_name'])){
            return;
        }
            
        $errMessage = $this->validateShippingData($postData);
        if ($errMessage != '') {
            $this->messageManager->addErrorMessage($errMessage);
            return;
        }

        $supplierId = $customer->getId();

        if (isset($postData['method_type']) === false) {
            return;
        }

        $supplierRate = $this->marketplaceRatesFactory->create()->load($supplierId,'supplier_id');

        $supplierMethods = $this->methodsCollectionFactory
            ->create()
            ->addFieldToFilter('supplier_id', $supplierId)
            ->getItems();

        $serializeData = false;
        $postedSupplierMethods = $postData['method_type'];
        foreach ($postedSupplierMethods as $key => $value) {
            // add new method
            if ($key == 0 && $postData['methods_to_delete'][0] != 'delete') {
                if ($this->isNewShippingMethod($postData)) {
                    $newMethod = $this->marketplaceMethodsFactory->create();
                    if ($value == self::FREE_SHIPPING) {
                        $newMethod->setData('supplier_id', $supplierId)
                            ->setData('flat_rate_available', 0)
                            ->setData('flat_rate_fee', 0)
                            ->setData('table_rate_available', 0)
                            ->setData('table_rate_fee', 0)
                            ->setData('table_rate_condition', 0)
                            ->setData('free_shipping', 1)
                            ->setData('name', $postData['method_name'][$key])
                            ->save();
                    }
                    if ($value == self::TABLE_RATE) {
                        $newMethod->setData('supplier_id', $supplierId)
                            ->setData('flat_rate_available', 0)
                            ->setData('flat_rate_fee', 0)
                            ->setData('table_rate_available', 1)
                            ->setData('table_rate_fee', $postData['tablerate_fee'][$key])
                            ->setData('table_rate_condition', $postData['tablerate_condition'][$key])
                            ->setData('free_shipping', 0)
                            ->setData('name', $postData['method_name'][$key])
                            ->save();
                        // get serialize data from csv
                        $serializeData =  $this->parseUploadedCsv($key);
                        if ($serializeData) {
                            if (!$supplierRate->getId()) {
                                $supplierRate->setSupplierId($supplierId);
                            }
                            $supplierRate->setRateData($serializeData);
                            $supplierRate->save();
                        }
                    }
                    if ($value == self::FLAT_RATE) {
                        $newMethod->setData('supplier_id', $supplierId)
                            ->setData('flat_rate_available', 1)
                            ->setData('flat_rate_fee', $postData['flatrate_fee'][$key])
                            ->setData('table_rate_available', 0)
                            ->setData('table_rate_fee', 0)
                            ->setData('table_rate_condition', 0)
                            ->setData('free_shipping', 0)
                            ->setData('name', $postData['method_name'][$key])
                            ->save();
                    }
                }
            }

            // update existed methods
            if (array_key_exists($key, $supplierMethods)) {
                $methodToUpdate = $supplierMethods[$key];
                if (isset($postData['methods_to_delete'][$key])
                    && $postData['methods_to_delete'][$key] == 'delete'
                ) {
                    $methodToUpdate->delete();
                    // delete supplier shipping rates
                    if($postData['method_type'][$key] == self::TABLE_RATE) {
                        if ($supplierRate->getId()) {
                            $supplierRate->delete();
                        }
                    }
                } else {
                    if ($value == self::FREE_SHIPPING) {
                        $methodToUpdate
                            ->setData('flat_rate_available', 0)
                            ->setData('flat_rate_fee', 0)
                            ->setData('table_rate_available', 0)
                            ->setData('table_rate_fee', 0)
                            ->setData('table_rate_condition', 0)
                            ->setData('free_shipping', 1)
                            ->setData('name', $postData['method_name'][$key])
                            ->save();
                    } elseif ($value == self::TABLE_RATE) {
                        $tableRateFee = $postData['tablerate_fee'][$key];
                        $tableRateCondition = $postData['tablerate_condition'][$key];
                        $methodToUpdate
                            ->setData('flat_rate_available', 0)
                            ->setData('flat_rate_fee', 0)
                            ->setData('table_rate_available', 1)
                            ->setData('table_rate_fee', $tableRateFee)
                            ->setData('table_rate_condition', $tableRateCondition)
                            ->setData('free_shipping', 0)
                            ->setData('name', $postData['method_name'][$key])
                            ->save();
                        // get serialize data from csv
                        $serializeData =  $this->parseUploadedCsv($key);
                        if ($serializeData) {
                            if (!$supplierRate->getId()) {
                                $supplierRate->setSupplierId($supplierId);
                            }
                            $supplierRate->setRateData($serializeData);
                            $supplierRate->save();
                        }
                    } elseif ($value == self::FLAT_RATE) {
                        $flatRateFee = $postData['flatrate_fee'][$key];
                        $methodToUpdate
                            ->setData('flat_rate_available', 1)
                            ->setData('flat_rate_fee', $flatRateFee)
                            ->setData('table_rate_available', 0)
                            ->setData('table_rate_fee', 0)
                            ->setData('table_rate_condition', 0)
                            ->setData('free_shipping', 0)
                            ->setData('name', $postData['method_name'][$key])
                            ->save();
                    }
                }
            }
        }
        // delete shipping table rates if this method is absent
        if ($this->isTableRatesMethodAbsent($supplierId)) {
            if ($supplierRate->getId()) {
                $supplierRate->delete();
            }
        }
    }

    /**
     * Parse uploded CSV
     *
     * @param $i
     *
     * @throws LocalizedException
     * @throws \Exception
     */
    private function parseUploadedCsv($i)
    {
        try {
            $uploader = $this->uploaderFactory->create(array('fileId' => 'tablerate_csv_file[' . $i . ']'));
            $file = $uploader->validateFile();

            $parsedData = [];
            $changed = false;
            if (isset($file['name'])) {
                if (file_exists($file['tmp_name'])) {
                    if (($handle = fopen($file['tmp_name'],'r')) !== false) {
                        while (($row = fgetcsv($handle)) !== false) {
                            $parsedData[] = $row;
                        }
                        $changed = true;
                        fclose($handle);
                    } else {
                        throw new LocalizedException(__('Cannot handle uploaded CSV'));
                    }
                    if ($parsedData[0][0] === 'Country') {
                        unset($parsedData[0]);
                    }
                }
            }

            if ($changed) {
                return serialize($parsedData);
            }
        } catch (\Exception $exception) {
            //ignore the method if the file was not uploaded or there is any errors during its parsing.
            return false;
        }

        return false;
    }

    /**
     * Is it a new shipping method
     *
     * @return boolean
     */
    protected function isNewShippingMethod($data)
    {
        if ($data['methods_to_delete'][0] == 'delete') {
            return false;
        }

        if ((!$data['method_name'][0] || $data['method_name'][0] == '') && $data['method_type'][0] == 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate shipping methods data before save
     *
     * @return string
     */
    protected function validateShippingData($data)
    {
        $message = '';
        $freeShipping = 0;
        $flatRateAvailable = 0;
        $tableRateAvailable = 0;

        foreach ($data['method_type'] as $id => $methodType) {
            if ($data['methods_to_delete'][$id] != 'delete') {
                if ($id == 0 && (!$data['method_name'][$id] || $data['method_name'][$id] == '') && $methodType == 0) {
                    continue;
                }

                if(!$data['method_name'][$id] || $data['method_name'][$id] == '') {
                    $message = 'Shipping Method Name can\'t be empty.';
                    return $message;
                }

                if($methodType == 0) {
                    $message = 'Shipping Method Type for ' . $data['method_name'][$id] . ' is not chosen.';
                    return $message;
                }
                if ($methodType == self::FREE_SHIPPING) {
                    $freeShipping++;
                }
                if ($methodType == self::TABLE_RATE) {
                    $tableRateAvailable++;
                }
                if ($methodType == self::FLAT_RATE) {
                    $flatRateAvailable++;
                }

                if($freeShipping > 1 || $flatRateAvailable > 1 || $tableRateAvailable > 1) {
                    $message = 'You can\'t save 2 similar shipping methods';
                    return $message;
                }
            }
        }

        return $message;
    }

    /**
     * Checking if table rates shipping methods is absent
     *
     * @return boolean
     */
    protected function isTableRatesMethodAbsent($supplierId)
    {
        $tableRateAvailable = 0;
        $shipping = $this->getSavedMethods($supplierId)->getData(); // get supplier saved methods
        foreach ($shipping as $item => $mass) {
            if (isset($mass['table_rate_available']) && $mass['table_rate_available'] == 1) {
                $tableRateAvailable++;
            }
        }
        if ($tableRateAvailable > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get supplier saved methods.
     *
     * @return AbstractCollection
     */
    public function getSavedMethods($supplierId)
    {
        $records = $this->marketplaceMethodsFactory->create()->getCollection();
        $records->addFilter('supplier_id', $supplierId);

        return $records;
    }
}
