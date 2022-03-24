<?php
namespace Kellton\Rpditems\Observer;

class Saveorderitemobserver implements \Magento\Framework\Event\ObserverInterface
{

	protected $_product;
    protected $_stockstate;   

    public function __construct(        
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Api\StockStateInterface $StockStateInterface
    ) {       
        $this->_product  = $product;
        $this->_stockstate = $StockStateInterface;  
        
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
	  {	     

	  	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	  	
	  	$order = $observer->getEvent()->getOrder();
			$orderdate=$order->getCreatedAt();
			foreach($order->getAllItems() as $item) {
				$products = $this->_product->load($item->getProductId());				
			    
			    $productqty = $this->_stockstate->getStockQty($item->getProductId(), $products->getStore()->getWebsiteId());

				$readinessdays = $products->getSupplier_ready_in_days();
				$readinessdate='';
				$deliverydate='';
				
				if($productqty>0){
					//Mage::log('out of stock', null, 'dateslog.log', true);
					$ccdate = new \DateTime($orderdate);
					$ccdate->add(new \DateInterval('P7D'));
					$deliverydate=$ccdate->format('Y-m-d');
				}else{
					//Mage::log('in stock', null, 'dateslog.log', true);
					if($readinessdays){
						$cdate = new \DateTime($orderdate);
						$cdate->add(new \DateInterval('P'.$readinessdays.'D'));
						$readinessdate=$cdate->format('Y-m-d');
						$ddays=$readinessdays+7;
						$ccdate = new \DateTime($orderdate);
					    $ccdate->add(new \DateInterval('P'.$ddays.'D'));
					    $deliverydate=$ccdate->format('Y-m-d');					
					}else{
						$cdate = new \DateTime($orderdate);
						$cdate->add(new \DateInterval('P5D'));
						$readinessdate=$cdate->format('Y-m-d');
						$ccdate = new \DateTime($orderdate);
					    $ccdate->add(new \DateInterval('P12D'));
					    $deliverydate=$ccdate->format('Y-m-d');
					}
					$mmm='readinessdate='.$readinessdate.' deliverydate='.$deliverydate;
					///Mage::log($mmm, null, 'dateslog.log', true);
				}
				$pickupdate = $products->getPickupdate();
				$item->setItemreadinessdate($readinessdate);
				$item->setItempickupdate($pickupdate);
				$item->setItemdeliverydate($deliverydate);
			} 

	     return $this;
	  }
}