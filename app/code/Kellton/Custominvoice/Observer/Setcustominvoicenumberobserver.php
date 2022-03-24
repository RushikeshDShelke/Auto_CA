<?php
namespace Kellton\Custominvoice\Observer;

class Setcustominvoicenumberobserver implements \Magento\Framework\Event\ObserverInterface
{

 	protected $_customcinvoice; 
 	protected $_customcminvoice;
 	private $logger;

    public function __construct(
	      	\Magento\Catalog\Block\Product\Context $context,
	      	\Psr\Log\LoggerInterface $logger,
		    \Kellton\Custominvoice\Model\Customcinvoice $customcinvoice,
		    \Kellton\Custominvoice\Model\Customcminvoice $customcminvoice
       	) {
    	$this->logger            =  $logger;
       	$this->_customcinvoice   =  $customcinvoice;
       	$this->_customcminvoice  =  $customcminvoice;       
    }


	  public function execute(\Magento\Framework\Event\Observer $observer)
	  {
  	    $invoice = $observer->getEvent()->getInvoice();
		$invoice_id = $invoice->getData('increment_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			if($invoice_id){
				try {
						$test  = $this->getLastCustomInvoiceNumber();
						$array_cum = explode("2021",$test->getCustomInvoiceNumber());
						if(isset($array_cum[1]) && $array_cum[1] != NULL ){
							$obj1 = $this->_customcinvoice;
							$obj1->setInvoiceNumber($invoice_id);
							$obj1->setCPrefix("CM".date("Y"));
							$obj1->setCustomInvoiceNumber("CM".date("Y") . "0000000" . ($array_cum[1] + 1));
							$obj1->setCreatedAt(time());
							$obj1->save();
						}
				 	


					/** Set Supplier Custom invoice id */
					$cobj = $this->_customcminvoice;
					$cobj->setInvoiceNumber($invoice_id);
					$cobj->setCmPrefix("CM".date("y"));
					$cobj->setCreatedAt(time());
					$cobj->save();
					$cobj2 = $this->_customcminvoice->load($invoice_id,'invoice_number');
				 	$obj5 = $this->_customcinvoice->load($invoice_id,'invoice_number');
					$cobj2->setCustomInvoiceNumber($obj5->getCustomInvoiceNumber()); // not working
					$cobj2->save();

			    } catch (\Exception $e) {
			        $this->logger->critical($e->getMessage());
			    }
			
		}
		return $this;
	}

	public function getLastCustomInvoiceNumber(){
		return $this->_customcinvoice->getCollection()->getLastItem();
	}
}
