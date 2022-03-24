<?php

namespace Cminds\Marketplace\Controller\Settings;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\Fields;
use Cminds\Marketplace\Model\MethodsFactory as MarketplaceMethods;
use Cminds\Marketplace\Model\RatesFactory as MarketplaceRates;
use Cminds\Marketplace\Model\Upload\CsvValidator;
use Cminds\MultiUserAccounts\Model\Import\Source\Csv;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Helper\Price;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Methodssave extends AbstractController
{
    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var DirectoryList
     */
    private $dir;

    /**
     * @var Fields
     */
    private $fields;

    /**
     * @var MarketplaceMethods
     */
    private $marketplaceMethods;

    /**
     * @var MarketplaceHelper
     */
    private $marketplaceHelper;

    /**
     * @var MarketplaceRates
     */
    private $marketplaceRates;

    private $priceHelper;

    /**
     * Csv Validator.
     *
     * @var CsvValidator
     */
    private $csvValidator;

    /**
     * Result redirect factory object.
     *
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Resource Connect
     *
     * @var resource
     */
    protected $resource;

    /**
     * Connection to database
     *
     * @var dbConnection
     */
    protected $dbConnection;

    /**
     * Methodssave constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param Transaction $transaction
     * @param Session $session
     * @param Customer $customer
     * @param DirectoryList $directoryList
     * @param Fields $fields
     * @param MarketplaceMethods $marketplaceMethods
     * @param MarketplaceHelper $marketplaceHelper
     * @param MarketplaceRates $marketplaceRates
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CsvValidator $csvValidator
     * @param ResourceConnection $resource
     * @param Price $priceHelper
     */
    public function __construct(
        Context $context,
        Data $helper,
        Transaction $transaction,
        Session $session,
        Customer $customer,
        DirectoryList $directoryList,
        Fields $fields,
        MarketplaceMethods $marketplaceMethods,
        MarketplaceHelper $marketplaceHelper,
        MarketplaceRates $marketplaceRates,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CsvValidator $csvValidator,
        ResourceConnection $resource,
        Price $priceHelper
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->transaction = $transaction;
        $this->customerSession = $session;
        $this->customer = $customer;
        $this->dir = $directoryList;
        $this->fields = $fields;
        $this->marketplaceMethods = $marketplaceMethods;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->marketplaceRates = $marketplaceRates;
        $this->priceHelper = $priceHelper;
        $this->csvValidator = $csvValidator;
        $this->resource = $resource;
        $this->dbConnection = $this->resource->getConnection();
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $postData = $this
            ->getRequest()
            ->getParams();

        $value = $this->scopeConfig
            ->getValue('configuration_marketplace' . '/presentation/allow_supplier_manage_shipping_costs');
        if (!$value) {
            $this->messageManager
                ->addErrorMessage(__('Managing shipping methods by supplier is currently disabled by admin.'));

            return $this->_redirect('supplier');
        }

        try {
            $errMessage = $this->validateShippingData($postData);
            if ($errMessage !== '') {
                throw new LocalizedException(__($errMessage));
            }

            $removedItems = array_diff(explode(',', $postData['removedItems']), array('', NULL, false));
            $supplierId = $this->marketplaceHelper->getSupplierId();
            $shippingMethod = 'empty_method';

            $transaction = $this->transaction;

            if (!isset($postData['id'])) {
                $postData['id'] = array();
            }

            foreach ($postData['id'] as $id => $data) {
                $shippingName = $postData['shipping_name'][$id];
                $shippingMethod = reset($postData['shipping_method'][$id]);

                // check method on empty data
                if ($data === '' && $shippingName === '' && $shippingMethod === 'empty_method') {
                    continue;
                }

                // create model
                if ($data !== '') {
                    $shipping = $this->marketplaceMethods->create()
                        ->load($data);
                } else {
                    $shipping = $this->marketplaceMethods->create()
                        ->setSupplierId($supplierId);
                }

                $shipping
                    ->setName($shippingName)
                    ->setFlatRateFee(0)
                    ->setFlatRateAvailable(0)
                    ->setTableRateAvailable(0)
                    ->setTableRateCondition(0)
                    ->setTableRateFee(0.00)
                    ->setFreeShipping(0);

                if ($shippingMethod === "flat_rate") {
                    $shipping
                        ->setFlatRateAvailable(1)
                        ->setFlatRateFee(
                            $this->priceHelper->convertToBaseCurrencyPrice($postData['flat_rate_fee'][$id])
                        );
                } elseif ($shippingMethod === "table_rate") {
                    $shipping->setTableRateAvailable(1);

                    if(isset($postData['table_rate_fee'][$id])) {
                        $shipping->setTableRateFee(
                            $this->priceHelper->convertToBaseCurrencyPrice($postData['table_rate_fee'][$id])
                        );
                    }
                    if(isset($postData['table_rate_condition'][$id])) {
                        $shipping->setTableRateCondition($postData['table_rate_condition'][$id]);
                    } else {
                        $shipping->setTableRateCondition(1);
                    }

                    // save serialize data from csv
                    $serializeData = $this->parseUploadedCsv($id);
                    if ($serializeData) {
                        $supplierRate = $this->marketplaceRates->create()
                            ->load($supplierId,'supplier_id');
                        if(!$supplierRate->getId()) {
                            $supplierRate->setSupplierId($supplierId);
                        }
                        $supplierRate->setRateData($serializeData);

                        $transaction->addObject($supplierRate);
                    }

                } elseif ($shippingMethod === "free_shipping") {
                    $shipping->setFreeShipping(1);
                }

                $transaction->addObject($shipping);
            }

            try {
                // START TRANSACTION
                $this->dbConnection->beginTransaction();

                // save changed data
                $transaction->save();

                // delete removed methods
                foreach ($removedItems as $item) {
                    if (!$item) {
                        continue;
                    }

                    $shipping = $this->marketplaceMethods->create()
                        ->load($item);
                    if (!$shipping->getId()) {
                        continue;
                    }

                    $shipping->delete();

                    // delete supplier shipping table rates
                    if($shipping->getTableRateAvailable()) {
                        $supplierRate = $this->marketplaceRates->create()
                            ->load($supplierId,'supplier_id');
                        if ($supplierRate->getId()) {
                            $supplierRate->delete();
                        }
                    }
                }

                // COMMIT TRANSACTION
                $this->dbConnection->commit();
                $this->messageManager->addSuccessMessage(__('Shipping methods was successfully saved'));
            } catch (Exception $e)  {
                // ROLLBACK TRANSACTION
                $this->dbConnection->rollback();
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->_redirect('marketplace/settings/methods/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            $this->_redirect('marketplace/settings/methods/');
        }
    }

    /**
     * Parse uploaded csv file.
     *
     * @param $i
     *
     * @return bool|string
     * @throws LocalizedException
     */
    protected function parseUploadedCsv($i)
    {
        $files = $this
            ->getRequest()
            ->getFiles() ?: [];
        $parsedData = array();

        if (isset($files['table_rate_file'][$i]['name']) && $files['table_rate_file'][$i]['name'] == '' ) {
            return false;
        }

        if (!isset($files['table_rate_file'][$i]['name'])) {
            throw new LocalizedException(__('File was not uploaded to the server'));
        }

        if (!isset($files['table_rate_file'][$i]["type"])) {
            throw new LocalizedException(__('File doesn\'t have type defined'));
        }

        $mimeType = $files['table_rate_file'][$i]["type"];
        if (!$this->csvValidator->validateFileType($mimeType)) {
            throw new LocalizedException(
                __('You have uploaded an invalid file. Only upload .csv format file')
            );
        }

        if (!file_exists($files['table_rate_file'][$i]['tmp_name'])) {
            return false;
        }

        $changed = false;
        if (($handle = fopen($files['table_rate_file'][$i]['tmp_name'],'r')) !== false) {
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

        if ($changed) {
            return serialize($parsedData);
        }

        return false;
    }

    /**
     * Redirect
     *
     * @param string $redirectUrl Redirect url.
     *
     * @return Redirect
     */
    protected function getResultRedirect($redirectUrl)
    {
        $resultRedirect = $this->resultRedirectFactory->create()
            ->setUrl($redirectUrl);

        return $resultRedirect;
    }

    /**
     * Checking if table rates shipping methods is absent.
     *
     * @return boolean
     */
    protected function isTableRatesMethodAbsent()
    {
        $shipping = $this
            ->getSavedMethods()
            ->getData(); // get supplier saved methods

        foreach ($shipping as $item => $mass) {
            if (isset($mass['table_rate_available']) && $mass['table_rate_available'] == 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate shipping methods data.
     *
     * @return string
     */
    protected function validateShippingData($data)
    {
        $message = '';

        if(!$data) {
            $message = 'Information about shipping methods is empty.';

            return $message;
        }

        // make array and remove null data
        $removedItems = array_diff(explode(',', $data['removedItems']), array('', NULL, false));

        if(isset($data['id'])) {
            foreach ($data['id'] as $i => $k) {
                if (!in_array( $k, $removedItems )) {
                    $shippingName = $data['shipping_name'][$i];
                    $shippingMethod = reset($data['shipping_method'][$i]);

                    if ($k === '' && $shippingName === '' && $shippingMethod === 'empty_method') {
                        continue;
                    }

                    if ($shippingName === '') {
                        $message = 'Shipping name is empty. Please fill out Shipping Name field.';

                        return $message;
                    }

                    if ($shippingMethod === 'empty_method') {
                        $message = 'Shipping method is not chosen. Please choose any Shipping Method.';

                        return $message;
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Get supplier saved methods.
     *
     * @return AbstractCollection
     */
    public function getSavedMethods()
    {
        $records = $this->marketplaceMethods->create()
            ->getCollection()
            ->addFilter(
                'supplier_id',
                $this->marketplaceHelper->getSupplierId()
            );

        return $records;
    }
}
