<?php
namespace Cminds\Ordercomment\Controller;

use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class SaveComment extends Action{

	protected $resultPageFactory;
    protected $orderRepository;
    protected $orderItemRepository;
    protected $productRepository;
    protected $scopeInterface;
    protected $storeManager;
    private $scopeConfig;
    private $transportBuilder;

    public function __construct(
        Context $context,
 		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
 		OrderRepositoryInterface $OrderRepository,
 		OrderItemRepositoryInterface $orderItemRepository,
 		ProductRepositoryInterface $ProductRepository,
 		\Magento\Store\Model\ScopeInterface $ScopeInterface,
 		\Magento\Store\Model\StoreManagerInterface $storeManager,
 		ScopeConfigInterface $scopeConfig,
 		TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->orderRepository = $OrderRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $ProductRepository;
        $this->scopeInterface = $ScopeInterface;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
    }

    public function execute() {

        //$mess=$_POST['vendorcomment'];
        $mess = $this->getRequest()->getParam('vendorcomment');
		//$orderid=$_POST['orderid'];
		$orderid = $this->getRequest()->getParam('orderid');
		//$ord = Mage::getModel('sales/order')->load($orderid);
		$ord = $this->orderRepository->get($orderid);
		$incorderid=$ord->getIncrementId();
		//$itemid=$_POST['itemid'];
		$itemid = $this->getRequest()->getParam('itemid');
		//$itemcomment=$_POST['itemcomment'];
		$itemcomment = $this->getRequest()->getParam('itemcomment');
		//$orderitemstatus=$_POST['orderitemstatus'];
		$orderitemstatus = $this->getRequest()->getParam('orderitemstatus');
		//$orderItem = Mage::getModel('sales/order_item')->load($itemid);
		$orderItem = $this->orderItemRepository->get($itemid);
		$itemname=$orderItem->getName();
		$productsku=$orderItem->getSku();
		$productqty=intval($orderItem->getQtyOrdered());
		//$productload = Mage::getModel('catalog/product')->load($orderItem->getProductId());
		$productload = $this->productRepository->getById($orderItem->getProductId());
		$productimg='';
		if($productload->getImageUrl()!=''){
			$productimg = '<img src="'.$productload->getImageUrl().'" height="200px" />';
		}
		$orderItem->setVditemreadinessdate($itemcomment);
		$orderItem->setOrderitemstatus($orderitemstatus);
		$orderItem->save();
		try{
			$partial_shipment = false;
			foreach ($ord->getAllItems() as $item) {
					if(!$item->getOrderitemstatus())
					{
							$partial_shipment = true;
					}
			}
			if($partial_shipment){
					$ord->setStatus("partial_ready_shipment");
			}
			else{
					$ord->setStatus("ready_shipment");
			}
			$history = $ord->addStatusHistoryComment('Order status was set to Ready for shipment', false);
			$history->setIsCustomerNotified(null);
			$ord->save();
		}
		catch(Exception $e)
		{
				die($e->getMessage());
		}
		
		/*-- send email to admin start--*/
			$templateId = 4;          
			$senderName = $this->scopeConfig->getValue('trans_email/ident_support/name');
			$senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email');        
			$sender = array('name' => $senderName,
						'email' => $senderEmail);
			$recepientName = $this->scopeConfig->getValue('trans_email/ident_custom1/name');
			$recepientEmail = $this->scopeConfig->getValue('trans_email/ident_custom1/email');      
			
			// Get Store ID        
			$storeId = $this->storeManager->getStore()->getId();
		    $orderno='MCID-'.$incorderid;
		    
			// Set variables that can be used in email template
			$vars = array('productname' => $itemname, 'orderid'=> $incorderid, 'orderno'=> $orderno, 'productsku'=> $productsku, 'productqty'=> $productqty, 'productimg'=> $productimg);

			$transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($vars)
            ->setFrom('general')
            ->addTo($recepientEmail)
            ->getTransport();


					
			//$translate  = Mage::getSingleton('core/translate');
		 
			// Send Transactional Email
			//Mage::getModel('core/email_template')
			//	->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
					
			//$translate->setTranslateInline(true);
		/*-- send email to admin end--*/
		$this->messageManager->addSuccessMessage('Status Updated Successfully!');
		//Mage::getSingleton('core/session')->addSuccess('Status Updated Successfully!');
		//$url=Mage::getUrl('marketplace/order/view',array( 'id' => $orderid ));
		$url=Mage::getUrl('marketplace/order/index');
		$url=$this->getUrl('marketplace/order/index');
		//Mage::app()->getResponse()->setRedirect($url)->sendResponse();
		$this->getResponse()->setRedirect($url);

	  
    }	
}

