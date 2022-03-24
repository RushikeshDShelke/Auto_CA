<?php
/**
 * Copyright © Kellton Tech Pvt. Ltd. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kellton\ReadyTopickemail\Observer\Backend\Sales;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\StoreManagerInterface;

class OrderInvoicePay implements \Magento\Framework\Event\ObserverInterface
{

    protected $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager

    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testrEADY.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('hiiii');
        //Your observer code


        $invoice        = $observer->getEvent()->getInvoice();
        $order          = $invoice->getOrder();

        $incrementid    = $order->getIncrementId();
        $mobile         = $order->getShippingAddress()->getTelephone();
        $username       = $order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
        $shippingAddress= $order->getShippingAddress();
        $custemail      = $shippingAddress->getEmail();

        $custname       = $shippingAddress->getName();
        $street         = $order->getShippingAddress()->getStreetFull();
        $city           = $order->getShippingAddress()->getCity();
        $postcode       = $order->getShippingAddress()->getPostcode();
        $state          = $order->getShippingAddress()->getRegion();
        
        $countryId      = $order->getShippingAddress()->getCountryId();
        $orderdate      = date('d/M/y', strtotime($order->getCreatedAt()));                    
        $shippingaddresshtml = $custname.'<br>'.$street.'<br>'.$city.','.$state.','.$postcode.'<br>'.$countryId.'<br>T: '.$mobile;

        /** Logic Composer*/
        $itemdetails = '';
        $productnamecoll = '';

        // $logger->info($order->getItemdeliverydate());
        foreach($order->getAllItems() as $item)
        {
            $productid = $item->getProductId();   
            $_product  = $item->getProduct();
            $productname= $item->getName();
            if($productnamecoll == ''){
                $productnamecoll.= $productname;
            }else{
                $productnamecoll.= ', '. $productname;
            }

            $productQty = intval($item->getQtyOrdered());
            $item_id = $item->getOrder_item_id();
            $deliverydate = $item->getItemdeliverydate();
            $itemdetails.='<p>Product Name: '.$productname.'</p>';
            $itemdetails.='<p>Product Quantity: '.$productQty.'</p>';
            $itemdetails.='<p>Expected Delivery Date: '.$deliverydate.'</p>';
        }

        /** Email Send Code */
        $templateId     = 17;
        $store = $this->storeManager->getStore()->getId();
        $sender         = array('name' => $this->getStorename(), 'email' => $this->getStoreEmail());
        $templateVars   = array('incrementid' => $incrementid,'orderdate' => $orderdate,'customername' => $custname,'itemdetails' => $itemdetails,'shippingaddress' => $shippingaddresshtml);
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store);
        $from           = $sender;
        $to             = $custemail;

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                                ->setTemplateOptions($templateOptions)
                                ->setTemplateVars($templateVars)
                                ->setFrom($from)
                                ->addTo($to)
                                ->getTransport();

        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            // @TODO: Logger.
        }
        return;

        /** Send sms to customer below, future scope*/

    }

    public function getStorename()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_support/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_support/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

