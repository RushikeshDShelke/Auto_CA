<?php
namespace Cminds\Ordercomment\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Exception\MailException;

class SendCompletEmail extends Action{

	protected $resultPageFactory;
    protected $helper;
    protected $storeManager;
    protected $collectionFactory;
    protected $orderFactory;
    protected $orderRepository;
    private $scopeConfig;
    private $transportBuilder;

    public function __construct(
        Context $context,
 		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
 		ScopeConfigInterface $scopeConfig,
 		OrderRepositoryInterface $OrderRepository,
 		\Magento\Store\Model\StoreManagerInterface $storeManager,
 		TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $OrderRepository;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    public function execute() {

    	$orderId = $this->getRequest()->getParam('id');
			//$order = Mage::getModel('sales/order')->load($orderId);
    	$order = $this->orderRepository->get($orderId);
			$incrementid = $order->getIncrementId();
			$shippingAddress = $order->getShippingAddress();
			$mobile=$order->getShippingAddress()->getTelephone();
			//$mobile = 7838486821;
			$username= $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
				$custemail=$shippingAddress->getEmail();
				$custname=$shippingAddress->getName();
				$street = $order->getShippingAddress()->getStreetFull();
                $city = $order->getShippingAddress()->getCity();
				$postcode = $order->getShippingAddress()->getPostcode();
				$state = $order->getShippingAddress()->getRegion();
				$getCountryId = $order->getShippingAddress()->getCountryId();
				$orderdate=$order->getCreatedAt();
				$orderItems = $order->getAllVisibleItems();
				$itemhtml='';
				$productnamecoll='';
				foreach ($orderItems as $item) {
					$productname= $item->getName();
					if($productnamecoll==''){
						$productnamecoll.= $productname;
					}else{
						$productnamecoll.= ', '. $productname;
					}
					$itemhtml .='<p style="font-size: 14px; color: #575757; margin: 5px">Product Name: '.$item->getName().'</p>
						 <p style="font-size: 14px; color: #575757; margin: 5px">Product Quantity: '.intval($item->getQtyOrdered()).'</p>
						 <p style="font-size: 14px; color: #575757; margin: 5px">Delivery Date: '.$item->getItemdeliverydate().'</p>';
				}
		/*-- send email to admin start--*/
			$templateId = 12;          
			$senderName = $this->scopeConfig->getValue('trans_email/ident_support/name');
			$senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email');        
			$sender = array('name' => $senderName,
						'email' => $senderEmail);
			$recepientName = $custname;
			$recepientEmail = $custemail;
			//$recepientEmail = "anjana.chawla@kelltontech.com";      
			
			// Get Store ID        
			$storeId = $this->storeManager->getStore()->getId();
		    $orderno=$incrementid;
		    
			// Set variables that can be used in email template
			$vars = array('id' => $orderId,'orderno' => $incrementid,'itemdetails' => $itemhtml,'orderdate' => $orderdate,'custname' => $custname,'custemail' => $custemail,'street' => $street,'city' => $city,'postcode' => $postcode,'state' => $state,'getCountryId' => $getCountryId);
					
			//$translate  = Mage::getSingleton('core/translate');
			$transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId, 'type' => 'html'])
            ->setTemplateVars($vars)
            ->setFrom(['email' => $senderEmail, 'name' => $senderName])
            ->addTo($recepientEmail)
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            // @TODO: Logger.
        }
		 
			// Send Transactional Email
			//Mage::getModel('core/email_template')
				//->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
					
			//$translate->setTranslateInline(true);
		/*-- send email to admin end--*/
		
		$msg='Dear '.$username.', 

Congratulations! Your product has been delivered.
Order number: '.$incrementid.'
Product Name: '.$productnamecoll.'

For more details: Login to your account on CraftMaestros.com.

With appreciation,
care@craftmaestros.com';
               $path="http://bulkpush.mytoday.com/BulkSms/SingleMsgApi?feedid=374549&username=9810411189&password=Craft@biz!sm5&To=".$mobile."&Text=".urlencode($msg);
				$ch = curl_init($path);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
		
		//Mage::getSingleton('adminhtml/session')->addSuccess('Complete mail sent successfully');
		$this->messageManager->addSuccessMessage('Complete mail sent successfully');
		//$url=Mage::getBaseUrl().'admin/sales_order/view/order_id/'.$orderId;
		$url=$this->getUrl('sales/order/view', ['order_id' => $orderId]);
		//Mage::app()->getResponse()->setRedirect($url)->sendResponse(); 
		try{
			$order->addStatusToHistory($order::STATE_COMPLETE);
			$order->save();
		}
		catch(Exception $e)
		{
			die($e);
		}
		$this->getResponse()->setRedirect($url);
	  
    }

}

