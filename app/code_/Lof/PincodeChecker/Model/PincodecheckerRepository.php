<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\PincodeChecker\Model;

use Lof\PincodeChecker\Api\PincodecheckerRepositoryInterface;
use Lof\PincodeChecker\Api\Data\PincodecheckerSearchResultsInterfaceFactory;
use Lof\PincodeChecker\Api\Data\PincodecheckerInterfaceFactory;
use Lof\PincodeChecker\Helper\Config as ConfigHelper;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\PincodeChecker\Model\ResourceModel\Pincodechecker as ResourcePincodechecker;
use Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory as PincodecheckerCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class PincodecheckerRepository implements PincodecheckerRepositoryInterface
{

    protected $resource;

    protected $pincodecheckerFactory;

    protected $pincodecheckerCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataPincodecheckerFactory;

    private $storeManager;

    protected $configHelper;

    protected $productRepository;


    /**
     * @param ResourcePincodechecker $resource
     * @param PincodecheckerFactory $pincodecheckerFactory
     * @param PincodecheckerInterfaceFactory $dataPincodecheckerFactory
     * @param PincodecheckerCollectionFactory $pincodecheckerCollectionFactory
     * @param PincodecheckerSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param ConfigHelper $configHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ResourcePincodechecker $resource,
        PincodecheckerFactory $pincodecheckerFactory,
        PincodecheckerInterfaceFactory $dataPincodecheckerFactory,
        PincodecheckerCollectionFactory $pincodecheckerCollectionFactory,
        PincodecheckerSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        ConfigHelper $configHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->resource = $resource;
        $this->pincodecheckerFactory = $pincodecheckerFactory;
        $this->pincodecheckerCollectionFactory = $pincodecheckerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPincodecheckerFactory = $dataPincodecheckerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\PincodeChecker\Api\Data\PincodecheckerInterface $pincodechecker
    ) {
        /* if (empty($pincodechecker->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $pincodechecker->setStoreId($storeId);
        } */
        try {
            $this->resource->save($pincodechecker);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the pincodechecker: %1',
                $exception->getMessage()
            ));
        }
        return $pincodechecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($pincodecheckerId)
    {
        $pincodechecker = $this->pincodecheckerFactory->create();
        $pincodechecker->load($pincodecheckerId);
        if (!$pincodechecker->getId()) {
            throw new NoSuchEntityException(__('pincodechecker with id "%1" does not exist.', $pincodecheckerId));
        }
        return $pincodechecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $collection = $this->pincodecheckerCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $items = [];
        
        foreach ($collection as $pincodecheckerModel) {
            $pincodecheckerData = $this->dataPincodecheckerFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pincodecheckerData,
                $pincodecheckerModel->getData(),
                'Lof\PincodeChecker\Api\Data\PincodecheckerInterface'
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $pincodecheckerData,
                'Lof\PincodeChecker\Api\Data\PincodecheckerInterface'
            );
        }
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\PincodeChecker\Api\Data\PincodecheckerInterface $pincodechecker
    ) {
        try {
            $this->resource->delete($pincodechecker);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the pincodechecker: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($pincodecheckerId)
    {
        return $this->delete($this->getById($pincodecheckerId));
    }

    /**
     * {@inheritdoc}
     */
    public function checkPincode($sku, $pincode){
        $pincodeStatus = $this->getPincodeStatus($pincode);
        $productStatus = $this->getProductPincodeStatus($sku, $pincode);
        if($productStatus){
            $message = $this->configHelper->getMessage(false, $pincode);
        }else{
            $message = $this->configHelper->getMessage((bool)$pincodeStatus, $pincode);
        }
        $message = strip_tags($message);
        return $message;
    }

    /**
     * Get pincode status
     */
    public function getPincodeStatus($pincode)
    {
        $collection = $this->pincodecheckerCollectionFactory->create();
        $collection->addFieldToFilter('pincode', array('eq' => $pincode));
        
        if($collection->getData()){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Get pincode status by product
     */
    public function getProductPincodeStatus($sku, $pincode)
    {
        $product = $this->productRepository->get($sku);
        $pincodes = $product->getData('pincode');
        $pincodeArr = explode(',', $pincodes);

        if(in_array($pincode, $pincodeArr))
        {
            return true;
        }else{
            return false;
        }
            
    }
}
