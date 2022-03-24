<?php

namespace Kellton\SupportTheArtisan\Controller\Index;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Kellton\SupportTheArtisan\Model\Supporttheartisan;
Use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

//use Kellton\SupportTheArtisan\Model\Supporttheartisan;

class Responsesave extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{

    protected $_pageFactory;
    protected $resultRedirect;
    private $scopeConfig;
    private $transportBuilder;
    protected $storeManager;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		Supporttheartisan $model,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\Redirect $resultRedirect
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->model = $model;
        $this->resultRedirect = $resultRedirect;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
		return parent::__construct($context);
	}
	 public function createCsrfValidationException(
	    RequestInterface $request
	): ?InvalidRequestException {
	    return null;
	}

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
	{
	    return true;
	}
    public function execute()
	{
		$postData = $this->getRequest()->getParams();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testsupport.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('hiiii');
        $logger->info($postData);
    	
    	if($postData['STATUS'] == "TXN_SUCCESS"){
    		$this->model->load($postData['ORDERID']);
    		if(!$this->model->getId()){
    			$this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->_pageFactory->create();

                return $resultRedirect->setPath('*/*/');
    		}
    		$this->model->setTransactionId($postData['BANKTXNID']);
    		if($postData['PAYMENTMODE'] == "PPI"){
    			$payment_mode = "Paytm Wallet";
    		}
    		if($postData['PAYMENTMODE'] == "CC"){
    			$payment_mode = "Credit Card";
    		}
    		if($postData['PAYMENTMODE'] == "DC"){
    			$payment_mode = "Debit Card";
    		}
    		if($postData['PAYMENTMODE'] == "NB"){
    			$payment_mode = "Net Banking";
    		}
    		if($postData['PAYMENTMODE'] == "UPI"){
    			$payment_mode = "UPI";
    		}
    		if($postData['PAYMENTMODE'] == "PAYTMCC"){
    			$payment_mode = "Postpaid";
    		}
    		$this->model->setPaymentMode($payment_mode);
    		$this->model->setStatus('Success');
    		$this->model->save();
            $storeId = $this->storeManager->getStore()->getId();
            $templateId = 25; 
            $senderName = $this->scopeConfig->getValue('trans_email/ident_support/name');
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email');        
            $sender = array('name' => $senderName,
                        'email' => $senderEmail);
            $recepientName = ucwords($this->model->getCustomerName());
            $recepientEmail = $this->model->getEmailId();
            $vars = array('customer_name' => $recepientName, 'donated_amount' => floatval($this->model->getAmount()) );
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($vars)
            ->setFrom('general')
            ->addTo($recepientEmail)
            ->getTransport();

            $transport->sendMessage();

    		return $this->_pageFactory->create();

    	}else{
            $this->model->load($postData['ORDERID']);
            if(!$this->model->getId()){
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirect->create();

                return $resultRedirect->setPath('*/*/');
            }
            $this->model->settransactionId($postData['BANKTXNID']);
            //$this->model->setPaymentMode($payment_mode);
            $this->model->setStatus('Failed');
            $this->model->save();
            //$resultRedirect = $this->_pageFactory->create();
           // $this->messageManager->addError("Somthing Went Wrong. Please try again");
            //$resultfact = $this->resultRedirect->create();
            return $this->resultRedirect->setPath('supporttheartisan');
        }
    	

	}
	
}
