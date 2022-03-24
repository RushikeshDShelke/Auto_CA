<?php
namespace Kellton\InventoryDeductionAfterPayment\Observer;

class Deductinventoryobserver implements \Magento\Framework\Event\ObserverInterface
{ 
       
    protected $_registry;
    protected $_stockItem;
    protected $_checkoutsession;
    protected $_quoteItem;
    protected $_checkoutCart;

    public function __construct(        
        \Magento\Framework\Registry $Registry,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Checkout\Model\Session  $checkoutSession,
        \Magento\Quote\Model\Quote\Item  $quoteItem,
        \Magento\Checkout\Model\Cart    $checkoutCart
    ) {       
        $this->_registry  = $Registry;
        $this->_stockItem = $stockItemRepository;
        $this->stockRegistry = $stockRegistry; 
        $this->_checkoutsession   = $checkoutSession;
        $this->_quoteItem    = $quoteItem;
        $this->_checkoutCart = $checkoutCart;
    }


  public function execute(\Magento\Framework\Event\Observer $observer)
   {     
     /*check registry variable and 
        on first time Mage::registry('prevent_observer') does not save value 
        So, it will execuate only on first time 
        */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

       // $registry      = $this->_registry->create();
        
        //$checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cronss.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('intime1');
        if(!$this->_registry->registry('prevent_observer')):
            $order = $observer->getEvent()->getOrder();
            $stateProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;
            // Assign value to registry variable
            $this->_registry->registry('prevent_observer');          
            // set quote to active
		 $ordered_items = $order->getAllItems();
                foreach ($ordered_items as $item) {
                    // Get stock data for given product.
                    $productStock = $this->stockRegistry->getStockItem($item->getProductId());
                    $orderedQty = $item->getQtyOrdered(); //ordered qty of item
                    $totalQty   = $productStock->getQty();
                    $actualQty  = $totalQty - $orderedQty;
		    $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
        	    $qty = $StockState->execute($item->getSku());
			//echo $qty[0]['qty']." ".$orderedQty; die;
		    $actualQty  = ((int)$qty[0]['qty'] - (int)$orderedQty);
		    $baseUrlVar             = 'http://efuat.craftmaestros.com/';
		    $baseUrlVar             = 'https://www.earthfables.com/';
            $logger->info('curl start');
		    //exec ('/usr/bin/php -f /var/www/html/ProductQtySync.php '.$item->getSku().' '.$actualQty);
                    //exec ("curl --data 'sku=".$item->getSku()."&qty=".$actualQty."' https://earth-fables.craftmaestros.com/ProductQtySyncCraft.php",$result);
		    exec ("curl --data 'sku=".$item->getSku()."&salableqty=".$actualQty."&mainqty=".$totalQty."&event=order_placed&orderid=".$order->getId()."' ".$baseUrlVar."ProductQtySyncCraftSalable.php");
            
            $logger->info("curl --data 'sku=".$item->getSku()."&salableqty=".$actualQty."&mainqty=".$totalQty."&event=order_placed&orderid=".$order->getId()."' ".$baseUrlVar."ProductQtySyncCraftSalable.php");
            $logger->info('curl end');
            $resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
            
                    $connection     = $resource->getConnection();
                    $sku_history = $resource->getTableName('sku_inventory_history'); //gives table name with prefix
                    $objDate = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
                    date_default_timezone_set('Asia/Kolkata');
                    $insertData     = ["sku"=>$item->getSku(),
                                        "reduced_qty"=> $item->getQtyOrdered(),
                                        "increased_qty"=> 0,
                                        "updated_salable_qty" => $actualQty,
                                        "action_comment"=>"Order Placed : ".$order->getIncrementId(),
                                        "created_at" =>date("Y-m-d h:i:s")
                                ];
                    date_default_timezone_set('UTC');
                    $result = $connection->insert($sku_history, $insertData);
		    /* $this->stockRegistry->getStockItem($item->getProductId())->setQty($actualQty)->save(); */
            $logger->info('inside foreach'.'productname = '.$item->getSku());
            $logger->info('=============');    
        }
            if($order->getState() == $stateProcessing && $order->getOrigData('state') != $stateProcessing) {

                $ordered_items = $order->getAllItems();                
                foreach ($ordered_items as $item) {

                    // Get stock data for given product.
                    $productStock = $this->stockRegistry->getStockItem($item->getProductId());
                    $orderedQty = $item->getQtyOrdered(); //ordered qty of item
                    $totalQty   = $productStock->getQty();
                    $actualQty  = $totalQty - $orderedQty;

                    /* $this->stockRegistry->getStockItem($item->getProductId())->setQty($actualQty)->save(); */
                }

                foreach( $this->_checkoutsession->getQuote()->getItemsCollection() as $item ){
                    $this->_checkoutCart->removeItem( $item->getId() )->save();
                    $logger->info('remove item');
                    $logger->info('=============');  
                }

                $allItems = $this->_checkoutsession->getQuote()->getAllVisibleItems();
                
                    foreach ($allItems as $item) {
                        //item id of particular item
                        $itemId = $item->getItemId();
                        //load particular item which you want to delete by his item id
                        $quoteItem = $this->_quoteItem->load($itemId);
                        //deletes the item
                        $quoteItem->delete();
                        $logger->info('quote delete');
                    $logger->info('=============');  
                    }
                    $logger->info('intime1');
                return true;
            }

        endif;
        $logger->info('intime2');
     return $this;
  }
}

// private $quoteItemFactory;
// private $itemResourceModel;
// public function __construct(
//   .....
//   \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
//   \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel
//   ......
// ) {
//    ....
//    $this->quoteItemFactory = $quoteItemFactory;
//    $this->itemResourceModel = $itemResourceModel
//    ...
// }
// $itemId = your id here
// $quoteItem = $this->quoteItemFactory->create();
// $this->itemResourceModel->load($quoteItem, $itemId);
