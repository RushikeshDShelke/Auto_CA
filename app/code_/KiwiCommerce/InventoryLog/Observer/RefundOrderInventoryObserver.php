<?php
/**
 * KiwiCommerce
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customise this module for your needs.
 * Please contact us https://kiwicommerce.co.uk/contacts.
 *
 * @category   KiwiCommerce
 * @package    KiwiCommerce_InventoryLog
 * @copyright  Copyright (C) 2018 KiwiCommerce Ltd (https://kiwicommerce.co.uk/)
 * @license    https://kiwicommerce.co.uk/magento2-extension-license/
 */

namespace KiwiCommerce\InventoryLog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use KiwiCommerce\InventoryLog\Helper\Data as InventoryLogHelper;

class RefundOrderInventoryObserver implements ObserverInterface
{
    /**
     * @var StockConfigurationInterface
     */
    public $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistryInterface;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepositoryInterface;

    /**
     * @var \KiwiCommerce\InventoryLog\Api\MovementRepositoryInterface
     */
    public $movementRepository;

    /**
     * @var InventoryLogHelper
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    public $productMetadata;

    /**
     * RefundOrderInventoryObserver constructor.
     * @param StockConfigurationInterface $stockConfiguration
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface
     * @param \KiwiCommerce\InventoryLog\Api\MovementRepositoryInterface $movementRepository
     * @param InventoryLogHelper $helper
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        ProductRepositoryInterface $productRepositoryInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface,
        \KiwiCommerce\InventoryLog\Api\MovementRepositoryInterface $movementRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        InventoryLogHelper $helper
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->stockRegistryInterface = $stockRegistryInterface;
        $this->movementRepository = $movementRepository;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       
        if ($this->helper->isModuleEnabled()) {
            /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
		$creditmemo = $observer->getEvent()->getCreditmemo();
            $itemsToUpdate = [];
            $order = $creditmemo->getOrder();
            foreach ($creditmemo->getAllItems() as $item) {
                $productId = $item->getProductId();
                $product = $this->productRepositoryInterface->getById($productId);
                $productType = $product->getTypeId();
                $qty = $item->getQty();
                
                if (($item->getBackToStock() && $qty)) {
                    
                    if ($qty && $productType == ProductType::TYPE_SIMPLE) {
                        $stockItem = $this->stockRegistryInterface->getStockItem($item->getProductId());
                        if ($this->productMetadata->getVersion() == '2.2.4') {
                            $oldQty = $stockItem->getQty();
                            $stockItem->setOldQty($oldQty);
                        } else {
                            $oldQty = $stockItem->getQty() - $qty;
                            $stockItem->setOldQty($oldQty);
                        }
                        /*$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
			$StockState 	= $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
			$qty            = $StockState->execute($item->getSku());
			$total_salable_qty = $qty[0]['qty'];
			exec ("curl --data 'sku=".$item->getSku()."&qty=".(int)$total_salable_qty."' https://earth-fables.craftmaestros.com/ProductQtySyncCraft.php");*/
			$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
                        $StockState     = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
                        $qty            = $StockState->execute($item->getSku());
                        $total_salable_qty = $qty[0]['qty'];
			$getSourceItemsDataBySku = $objectManager->get('\Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku');
        		$AllInventoryBySources  = $getSourceItemsDataBySku->execute($item->getSku());
        		$total_main_qty_CM = 0;
        		foreach($AllInventoryBySources as $sourceInventory)
        		{
                		$total_main_qty_CM = (int)$total_main_qty_CM + (int)$sourceInventory['quantity'];
			}
			$baseUrlVar             = 'http://earth-fables.craftmaestros.com/';
			$baseUrlVar             = 'https://www.earthfables.com/';
                        //exec ("curl --data 'sku=".$item->getSku()."&qty=".(int)$total_salable_qty."' https://earth-fables.craftmaestros.com/ProductQtySyncCraft.php");
			// exec ("curl --data 'sku=".$item->getSku()."&salableqty=".(int)$total_salable_qty."&mainqty=".$total_main_qty_CM."&event=refunded&orderid=' ".$baseUrlVar."ProductQtySyncCraftSalable.php");
			$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection     = $resource->getConnection();
                        $sku_history = $resource->getTableName('sku_inventory_history'); //gives table name with prefix
                        date_default_timezone_set('Asia/Kolkata');
                       
			$insertData     = ["sku"=>$item->getSku(),
                                         "reduced_qty"=> 0,
                                         "increased_qty"=> $item->getQty(),
                                         "updated_salable_qty" => $total_salable_qty,
                                         "action_comment"=>"Order Refunded : ".$order->getIncrementId(),
                                         "created_at" =>date("Y-m-d h:i:s")
                                         ];                                                     
                        $result = $connection->insert($sku_history, $insertData);
                        date_default_timezone_set('UTC');
                        $msg = __('Product restocked after credit memo creation (credit memo: %s)');
                        $message = sprintf(
                            $msg,
                            $creditmemo->getId()
                        );
                        
                        $this->movementRepository->insertStockMovement($stockItem, $message, 0, $qty);
                        $this->helper->unRegisterAllData();
                    }
                }
	    }
        
	    $order = $creditmemo->getOrder();
        
	    foreach($order->getAllVisibleItems() as $item)
	    {
		$partial_creditmemo = false;
	    	if($item->getQtyShipped() !=$item->getQtyRefunded())
                {
                        $partial_creditmemo = true;
                }
	    }
	    $order->setState("closed")->setStatus("partial_refunded");
	    $order->save();
        }
    }
}
