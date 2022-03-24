<?php
namespace Cminds\Supplierfrontendproductuploader\Model\Product;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Sources\Collection as cmSourcesCollection;
use Magento\Framework\App\ObjectManager;
use Cminds\Supplierfrontendproductuploader\Helper\Inventory as CmindsInventoryHelper;
// use Magento\InventoryApi\Api\SourceRepositoryInterface;
// use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
// use Magento\Inventory\Model\ResourceModel\Source\Collection as SourceCollection;
// use Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor;
// use Magento\InventorySalesApi\Api\StockResolverInterface;
// use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Cminds Supplierfrontendproductuploader product inventory model.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 */
class Inventory
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var Magento\InventoryApi\Api\GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var Magento\Inventory\Model\ResourceModel\Source\Collection
     */
    private $sourceCollection;

    /**
     * @var Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor
     */
    private $sourceItemsProcessor = null;
    
    /**
     * @var Magento\InventorySalesApi\Api\StockResolverInterface
     */
    private $stockResolver = null;

    /**
     * @var Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
     */
    private $productSalableQty = null;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ObjectManager
     */
    protected $objectManager;
    
    /**
     * @var CmindsInventoryHelper
     */
    private $cmindsInventoryHelper;

    /**
     * @var cmSourcesCollection
     */
    private $cmSourceCollection;


    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerSession $customerSession,
     * @param CmSourcesCollection $cmSourcesCollection
     * @param CmindsInventoryHelper $cmindsInventoryHelper
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerSession $customerSession,
        CmindsInventoryHelper $cmindsInventoryHelper,
        CmSourcesCollection $cmSourcesCollection
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cmSourceCollection = $cmSourcesCollection;
        $this->customerSession = $customerSession;
        $this->cmindsInventoryHelper = $cmindsInventoryHelper;

        // MSI backward compatibility 
        if( $this->cmindsInventoryHelper->msiFunctionalitySupported() ) {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->sourceRepository = $this->objectManager
                    ->create('Magento\InventoryApi\Api\SourceRepositoryInterface');
            $this->getSourceItemsBySku = $this->objectManager
                    ->create('Magento\InventoryApi\Api\GetSourceItemsBySkuInterface');
            $this->sourceCollection = $this->objectManager
                    ->create('Magento\Inventory\Model\ResourceModel\Source\Collection');
        }
    }

    /**
     * Get sources objects.
     *
     * @return Magento\Inventory\Model\SourceSearchResults
     */
    public function getInventorySources()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceInterface::ENABLED, true)
            ->create();

        return $this->sourceRepository->getList($searchCriteria);
    }

    /**
     * Get sources objects.
     *
     * @return array
     */
    public function getInventorySourcesFilteredArray()
    {
        $sourcesArray = $this->getInventorySources()->getItems();

        $sourceCollection = $this->cmSourceCollection->addFieldToFilter('customer_id', ['neq' => $this->customerSession->getId()]);

        // exclude all sources from cm and not associated with current uesr
        if ($sourceCollection->count()) {
            foreach ($sourceCollection as $item) {
                if (isset($sourcesArray[$item->getSourceCode()])) {
                    unset($sourcesArray[$item->getSourceCode()]);
                }
            }
        }

        return $sourcesArray;
    }


    /**
     * Check if mulriple inventory sources are configured.
     *
     * @return bool
     */
    public function inventoryIsSingleSourceMode()
    {   
        // force singe inventory method if functionality is not supported by magento version ( needed at least 2.3)
        if(false === $this->cmindsInventoryHelper->msiFunctionalitySupported() )
            return true;

        $searchResult = $this->getInventorySources();
        return $searchResult->getTotalCount() < 2;
    }

    /**
     * Check if mulriple inventory sources are configured.
     *
     * @param string $sku
     *
     * @return array
     */
    public function getSourcesBySku(string $sku)
    {
        return $this->getSourceItemsBySku->execute($sku);
    }

    /**
     * Get inventory source by code.
     *
     * @param string $sourceCode
     *
     * @return Magento\InventoryApi\Api\SourceRepositoryInterface;
     */
    public function getInventorySourceByCode(string $sourceCode)
    {
        return $this->sourceRepository->get($sourceCode);
    }

    /**
     * check if source code in use.
     *
     * @param string $sourceCode
     *
     * @return bool;
     */
    public function isSourceCodeUsed(string $sourceCode)
    {
        $sourceCollection = $this->sourceCollection->addFieldToFilter('source_code', $sourceCode);
        return (bool) $sourceCollection->count() > 0;
    }

    /**
     * Get SourceRepository.
     *
     * @return Magento\InventoryApi\Api\SourceRepositoryInterface;
     */
    public function getSourceRepositoryObject()
    {
        return $this->sourceRepository;
    }

    /**
     * Get GetSourceItemsBySkuInterface.
     *
     * @return Magento\InventoryApi\Api\GetSourceItemsBySkuInterface
     */
    public function getSourceItemsBySkuObject()
    {
        return $this->getSourceItemsBySku;
    }
    
    /**
     * Get Source Collection.
     *
     * @return Magento\Inventory\Model\ResourceModel\Source\Collection
     */
    public function getSourceCollectionObject()
    {
        return $this->sourceCollection;
    }

    /**
     * Get SourceItemsProcessor.
     *
     * @return Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor
     */
    public function getSourceItemsProcessorObject()
    {
        if( null === $this->sourceItemsProcessor ){
            //Load object
            $this->sourceItemsProcessor = $this->objectManager
                    ->create('Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor');
        }
        return $this->sourceItemsProcessor;
    }

    /**
     * Get StockResolverInterface.
     *
     * @return Magento\InventorySalesApi\Api\StockResolverInterface
     */
    public function getStockResolverObject()
    {
        if( null === $this->stockResolver ){
            //Load object
            $this->stockResolver = $this->objectManager
                    ->create('Magento\InventorySalesApi\Api\StockResolverInterface');
        }
        return $this->stockResolver;
    }
    
    /**
     * Get GetProductSalableQtyInterface.
     *
     * @return Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
     */
    public function getProductSalableQtyObject()
    {
        if( null === $this->productSalableQty ){
            //Load object
            $this->productSalableQty = $this->objectManager
                    ->create('Magento\InventorySalesApi\Api\GetProductSalableQtyInterface');
        }
        return $this->productSalableQty;
    }
}
