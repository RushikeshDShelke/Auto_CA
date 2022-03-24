<?php
namespace Cminds\Ordercomment\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;



class SaveComment extends Action{

	protected $resultPageFactory;
    protected $orderRepository;
    protected $orderItemRepository;
    protected $productRepository;
    protected $storeManager;
    private $scopeConfig;
    private $transportBuilder;
    protected $orderInterface;

    public function __construct(
        Context $context,
 		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
 		OrderRepository $OrderRepository,
 		OrderItemRepositoryInterface $orderItemRepository,
 		\Magento\Catalog\Model\ProductRepository $ProductRepository,
 		ScopeConfigInterface $scopeConfig,
 		\Magento\Store\Model\StoreManagerInterface $storeManager,
 		TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->orderRepository = $OrderRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $ProductRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
    }
 
    public function execute() {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testemaliaa.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('i am called');

        $mess = $this->getRequest()->getParam('vendorcomment');
		$orderid = $this->getRequest()->getParam('orderid');
		$itemid = $this->getRequest()->getParam('itemid');
		$itemcomment = $this->getRequest()->getParam('itemcomment');
		$orderitemstatus = $this->getRequest()->getParam('orderitemstatus');
		$orderItem = $this->orderItemRepository->get($itemid);
		$itemname=$orderItem->getName();
		$productsku=$orderItem->getSku();
		$productqty=intval($orderItem->getQtyOrdered());

		$productload = $this->productRepository->getById($orderItem->getProductId());

		$objectManager =\Magento\Framework\App\ObjectManager::getInstance();
		$helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');

		$imageUrl = $helperImport->init($productload, 'product_page_image_small')
                ->setImageFile($productload->getSmallImage()) // image,small_image,thumbnail
                ->resize(380)
                ->getUrl();
		
		$logger->info($imageUrl);


		$productimg='';
		//  if($productload->getImageUrl()!=''){
			$productimg = '<img src="'.$imageUrl.'" height="200px" />';
		// }
		
		$orderItem->setOrderitemstatus($orderitemstatus);
		$orderItem->save();
		$ord = $this->orderRepository->get($orderid);
		$incorderid=$ord->getIncrementId();
		$this->saveOrderData($ord);
		
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
            $transport->sendMessage();
		            
		/*-- send email to admin end--*/
		$this->messageManager->addSuccessMessage('Status Updated Successfully!');
		$url=$this->_redirect('marketplace/order');
    }	

    public function saveOrderData($ord){
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
    }
}

