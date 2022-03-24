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
			if($invoice_id){
				try {
					$last_custom_invoice_data   = $this->getLastCustomInvoiceNumber();					
					if (date('m') > 3) {
						$year = date('Y');
					}
					else {
						$year = (date('Y')-1);
					}				
					$financial_year_date = "CM".$year;					
					$custom_invocie_explode_array = explode($financial_year_date,$last_custom_invoice_data->getCustomInvoiceNumber());				
					if(isset($custom_invocie_explode_array[1]) && $custom_invocie_explode_array[1] != NULL ){
						$obj1 = $this->_customcinvoice;
						$obj1->setInvoiceNumber($invoice_id);
						$obj1->setCPrefix($financial_year_date);
						$last_custom_invoice_increment = $custom_invocie_explode_array[1] + 1;						
						//adding zero before increment_id
						$custom_invoice_with_zeros = sprintf('%010d' , $last_custom_invoice_increment);
						$obj1->setCustomInvoiceNumber($financial_year_date.$custom_invoice_with_zeros);
						$obj1->setCreatedAt(time());
						$obj1->save();
					}
				 	
					

					/** Set Supplier Custom invoice id */
					$cobj = $this->_customcminvoice;
					$cobj->setInvoiceNumber($invoice_id);
					$cobj->setCmPrefix($financial_year_date);
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
