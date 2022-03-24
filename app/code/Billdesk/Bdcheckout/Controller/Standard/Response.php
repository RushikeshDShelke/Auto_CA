<?php
namespace Billdesk\Bdcheckout\Controller\Standard;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Response extends \Billdesk\Bdcheckout\Controller\BdAbstract implements CsrfAwareActionInterface, HttpPostActionInterface{
	
	 /**
     * @inheritDoc
     */

    protected $_checkoutSession;
    protected $_logger;
    protected $orderFactory;
    protected $_transaction;
    protected $quoteFactory;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession, 
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Payment\Transaction $transaction,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {

        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;   
        $this->orderFactory = $orderFactory; 
        $this->_transaction = $transaction;   
        $this->quoteFactory = $quoteFactory;  
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
   
    
    /**
     * Verify the response coming into the server
     * @return boolean
     */
    protected function _validateResponse($res_authcode)
    {       
        $flag = False;       
        $this->_logger->debug('Response Code is ' . $res_authcode); 
        if ($res_authcode == '0300') { 
            $flag = True;
        }
        return $flag;
    }

    public function calculateChecksum($secret_key, $all) {
        $hash = hash_hmac('sha256', $all , $secret_key);
        $checksum = $hash;
        $checksum = strtoupper($checksum);
        return $checksum;
    }


    public function execute() {

        $postdata = $this->getRequest()->getPost();
        //print_r($postdata);
        //die;
        $session  = $this->_checkoutSession;
       
        $session->setQuoteId($session->getBillDeskQuoteId(true));
        
        $billdeskConfig_secretkey = $this->scopeConfig->getValue('payment/bdcheckout/checksum_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $bdresmsg       = $postdata['msg'];  

        $this->_logger->debug("billdesk pay res msg=" . $bdresmsg); 


        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');

        //start M1 Code


        if (isset($bdresmsg)) {
            $msg_splitted    = explode("|", $bdresmsg);
            $res_mercid      = $msg_splitted[0];
            $res_custid      = $msg_splitted[1];
            $res_bdtxnrefno  = $msg_splitted[2];
            $res_amount      = $msg_splitted[4];
            $res_bankid      = $msg_splitted[5];
            $res_paymode     = $msg_splitted[7];
            $res_authcode    = $msg_splitted[14];
            $res_description = $msg_splitted[24];
            $res_checksum    = $msg_splitted[25];
            $comment         = 'BillDesk Transaction Id : ' . $res_bdtxnrefno . '<br/>' . 'Payment Mode : ' . $res_paymode . '<br/>' . 'Bank Id : ' . $res_bankid . '<br/>' . 'Transaction Response Code : ' . $res_authcode . '<br/>' . 'Transaction Response Message : ' . $res_description;

            $this->_logger->debug("Response code=" . $res_authcode . " for orderid=" . $res_custid); 

            //error_log("Response code=" . $res_authcode . " for orderid=" . $res_custid);
            
            $bdres_wtcheck = substr($bdresmsg, 0, strrpos($bdresmsg, '|'));

            //$newcheck      = Checksum::calculateChecksum($billdeskConfig_secretkey, $bdres_wtcheck);

            $newcheck      = $this->calculateChecksum($billdeskConfig_secretkey, $bdres_wtcheck);
            
            $order         = $this->orderFactory->create()->loadByIncrementId($res_custid);          

            $this->_logger->debug("txn status before update=" . $order->getStatus() . " for orderid=" . $res_custid); 
            
            //Checksum verification.Proceed only if checksum matches. Else redirect to error page.
            if ($newcheck !== $res_checksum) {

                $this->_logger->debug("txn checksum mismatch calculated checksum = " . $newcheck . " and checksum received = " . $res_checksum . " for orderid=" . $res_custid); 

                // error_log("txn checksum mismach calculated checksum = " . $newcheck . " and checksum received = " . $res_checksum . " for orderid=" . $res_custid);

                if ($session->getLastRealOrderId()) {
                    $order = $this->orderFactory->create()->loadByIncrementId($session->getLastRealOrderId()); 

                    if ($order->getId()) {
                        $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, $comment)->save();
                    }
                }
                $er = 'Sorry, We are unable to process the request. Please try again later';
                $session->addError($er);
                
                //$this->_redirect('billdesk/transact/failure');
                $this->_redirect('checkout/onepage/failure');

                return;
            }
            
            $order_amount = $order->getGrandTotal();
            $order_amount = (int) ($order_amount * 100);
            $res_amount   = (int) ($res_amount * 100);
            
            //Txn amount verification.Proceed only if amount matches else redirect to error page.
            if ($res_amount !== $order_amount) {
               
                $this->_logger->debug("amount mismatch -- order amount=" . $order_amount . " and res amount=" . $res_amount . " for orderid=" . $res_custid); 


                if ($session->getLastRealOrderId()) {
                    
                    $order = $this->orderFactory->create()->loadByIncrementId($session->getLastRealOrderId()); 

                    if ($order->getId()) {
                        $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, $comment)->save();
                    }
                }

                $er = 'Sorry, We are unable to process the request. Please try again later';
                $session->addError($er);
                //$this->_redirect('billdesk/transact/failure');
                $this->_redirect('checkout/onepage/failure');
                return;
            }
            
            
            // success
            if ($this->_validateResponse($res_authcode)) {

                $this->_checkoutSession->getQuote()->setIsActive(false)->save();
                // load the order and change the order status
                //$billdesk = Mage::getModel('billdesk/transact');
                //$state    = $billdesk->billdeskSuccessOrderState();
                $state    = 'processing';
                //$order = Mage::getModel('sales/order')->loadByIncrementId($postdata['orderId']);
                //$order->setData('state', $state, true, $comment);
                $order->setState($state, true, $comment);

                 $this->_logger->debug("response comment=" . $comment . " for orderid=" . $res_custid); 

                //error_log("response comment=" . $comment . " for orderid=" . $res_custid);
                //$order->setStatus($state);
                // also do something similar to capturing the payment here            
                $payment      = $order->getPayment();
                //$transaction  = Mage::getModel('sales/order_payment_transaction');

                $transaction  = $this->_transaction;
                $dummy_txn_id = 'BD_' . $res_custid;

                $transaction->setOrderPaymentObject($payment)->setTxnId($dummy_txn_id)->setTxnType(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH)->setIsClosed(0)->save();
                $order->save();
                if($order->getHasChild())
                {
                    $childOrderIds=explode(',',$order->getChildIds());
                    foreach($childOrderIds as $childOrderId)
                    {
                        //$childOrder = Mage::getModel('sales/order')->loadByIncrementId($childOrderId);
                        $childOrder = $this->orderFactory->create()->loadByIncrementId($childOrderId); 

                        $childOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true, $comment);
                        $childOrder->save();
                        try {
                            $childOrder->sendNewOrderEmail();
                        }
                        catch (\Exception $ex) {
                             $this->messageManager->addExceptionMessage($ex, $ex->getMessage());
                        }
                        $this->_logger->debug("response comment=" . $comment . " for orderid=" . $childOrderId);
                        //error_log("response comment=" . $comment . " for orderid=" . $childOrderId);
                    }
                }
                else{
                    try {
                        $order->sendNewOrderEmail();
                    }
                    catch (\Exception $ex) {
                       // $this->logger->critical($ex->getMessage());
                        $this->messageManager->addExceptionMessage($ex, $ex->getMessage());
                    }
                }
                
                $this->_redirect('checkout/onepage/success', array(
                    '_secure' => true
                ));

                
            } else {
                // failure/cancel
                if ($session->getLastRealOrderId()) {
                    
                    $order = $this->orderFactory->create()->loadByIncrementId($session->getLastRealOrderId()); 

                    if ($order->getId()) {
                        $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, $comment)->save();
                    }
                }
                // set quote to active
                $order = $this->orderFactory->create()->loadByIncrementId($session->getLastRealOrderId()); 
                if (!$order->getId()) {
                    //Mage::throwException('No order for processing found');
                }
                if($order->getHasChild())
                {
                    $childOrderIds = explode(',',$order->getChildIds());
                    foreach($childOrderIds as $childOrderId)
                    {
                        
                        $childorder = $this->orderFactory->create()->loadByIncrementId($childOrderId); 

                        if ($childorder->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
                            $childorder->setState(\Magento\Sales\Model\Order::STATE_CANCELED,true)->save();
                        }
                    }
                }
                else{
                    $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();
                }
                if ($quoteId = $session->getQuoteId()) {

                    $quote = $this->quoteFactory->create()->load($quoteId);

                    if ($quote->getId()) {
                        $quote->setIsActive(true)->save();
                        $session->setQuoteId($quoteId);
                    }
                }
                
                $this->logger->debug('cancel');
                $er = 'Thank you for shopping with us. However, the transaction has been declined for reason :  ' . $res_description . '';
                $session->addError($er);
                $this->_redirect('checkout/cart');
                
            }
        } else {
           
            $this->logger->debug("Response msg is not set...");

            $er = 'Sorry, We are unable to process the request. Please try again later.';

            $session->addError($er);

            //$this->_redirect('billdesk/transact/failure');
            $this->_redirect('checkout/onepage/failure');
            
        }


        //End M1 Code



        // try {
        //     $paymentMethod = $this->getPaymentMethod();
        //     $params = $this->getRequest()->getParams();
        //     $status = $paymentMethod->validateResponse($params); 
  
        //     if ($status == "000" || $status == 000) {
        //         $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
        //         $order = $this->getOrder();
        //         $payment = $order->getPayment();
        //         $paymentMethod->postProcessing($order, $payment, $params);
        //     } else if ($status == "010" || $status == 010) {
        //         $this->getOrder()->cancel()->save();
        //         $this->messageManager->addErrorMessage(__('Your payment has been cancelled'));
        //     } else if ($status == "006" || $status == 006) {
        //         $this->messageManager->addErrorMessage(__('Your payment was successful and under review'));
        //     } else {
        //         $this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method'));
        //         $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
        //     }
        // } catch (\Magento\Framework\Exception\LocalizedException $e) {
        //     $this->messageManager->addExceptionMessage($e, $e->getMessage());
        // } catch (\Exception $e) {
        //     $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        // }

        //$this->getResponse()->setRedirect($returnUrl);
    }

}
