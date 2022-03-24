<?php

namespace Kellton\InvoiceEmailSend\Rewrite\Magento\Sales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;


/**
 * Class InvoiceSender
 *
 * @package Kellton\InvoiceEmailSend\Rewrite\Magento\Sales\Model\Order\Email\Sender
 */
class InvoiceSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var InvoiceResource
     */
    protected $invoiceResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;
    /**
     * @var Collection
     */
    private $collectionFactory;

    protected $_storeManager;
    protected $_currency;
    protected $_productRepository;
    protected $storels;
    protected $taxCalculation;
 

    /**
     * @param Template $templateContainer
     * @param InvoiceIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param InvoiceResource $invoiceResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        InvoiceIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        InvoiceResource $invoiceResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Store $storels,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Tax\Model\Calculation $taxCalculation
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $invoiceResource, $globalConfig, $eventManager);
        $this->paymentHelper = $paymentHelper;
        $this->invoiceResource = $invoiceResource;
        $this->globalConfig = $globalConfig;
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
        $this->collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->storels = $storels;
        $this->_currency = $currency;
        $this->_productRepository = $productRepository;
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * Sends order invoice email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Invoice $invoice
     * @param bool $forceSyncMode
     * @return bool
     * @throws \Exception
     */
    public function send(Invoice $invoice, $forceSyncMode = false)
    {
        $invoice->setSendEmail($this->identityContainer->isEnabled());
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        
        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $invoice->getOrder();
            $this->identityContainer->setStore($order->getStore());
            
            /**Custom Code for setting variable templates */
            $invoiceId = $invoice->getIncrementId();
            
            if ($invoice->getIncrementId()) {
                $objCustominvoice = $objectManager->get('Kellton\Custominvoice\Model\Customcinvoice')->load($invoiceId, 'invoice_number');
                if ($objCustominvoice->getCIncrId())
                    $invoiceId = $objCustominvoice->getCustomInvoiceNumber();
            }
            $ewayBillNo = '';

            foreach($order->getShipmentsCollection() as $shipment)
            {
                if(count($shipment->getData())){
                    if($shipment->getIncrementId()){
                        try{
                            $objEwaybillno = $objectManager->get('Kellton\Ewaybillno\Model\Ewaybillno')->load($shipment->getIncrementId(), 'shipment_id');
                            $ewayBillNo = $objEwaybillno->getEwayBillNo();
                        }
                        catch(Exception $ex){
                        
                        }
                    }
                }
            }
		$state_code  = 0;
		if($order->getShippingAddress()->getRegion())
		{
            		$state_code  = $this->getRegionCode($order->getShippingAddress()->getRegion());
		}
            $itemsdetail = $this->getInvoiceItemsHtml($order, $this, $state_code);

            $transport = [
                'order' => $order,
                'invoice' => $invoice,
                'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'invoiceid' => $invoiceId,
                'created_at' => $invoice->getCreatedAt(),
                'statecode' =>$this->getRegionID($order->getShippingAddress()->getRegion()),
                'itemsdetail' => $itemsdetail['itemsdetail'],
                'shippingamount' => $priceHelper->currency($itemsdetail['shipping_amount'], true, false),
                'shippingtaxamount' => $priceHelper->currency($itemsdetail['shipping_tax_amount'], true, false),
                'shippingtaxpercent' => $itemsdetail['shipping_tax_percent'],
                'SIGST' => $itemsdetail['SIGST'],
                'SSGST' => $itemsdetail['SSGST'],
                'SCGST' => $itemsdetail['SCGST'],
                'grandtotal' => $priceHelper->currency($itemsdetail['grandtotal'], true, false),
                'contactperson' => $order->getShippingAddress()->getName(),
                'telephone' => $order->getShippingAddress()->getTelephone(),
                'placeofsupply' => $order->getShippingAddress()->getRegion(),
                'ewaybillno' => $ewayBillNo,
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order)
            ];
            
            $transportObject = new DataObject($transport);
            
            /**
             * Event argument `transport` is @deprecated. Use `transportObject` instead.
             */
            $this->eventManager->dispatch(
                'email_invoice_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );
            $this->templateContainer->setTemplateVars($transportObject->getData());
            $this->templateContainer ->setTemplateOptions(['area' => 'frontend', 'store' => $this->identityContainer->getStore()->getStoreId(), 'type' => 'html']);

            if ($this->checkAndSend($order)) {
                $invoice->setEmailSent(true);
                $this->invoiceResource->saveAttribute($invoice, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $invoice->setEmailSent(null);
            $this->invoiceResource->saveAttribute($invoice, 'email_sent');
        }

        $this->invoiceResource->saveAttribute($invoice, 'send_email');

        return false;
    }

    /**
     * Return payment info block as html
     *
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }

    public function getInvoiceItemsHtml($order, $invoice, $state_code)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
		// $storels              = $this->_storeManager;
		// $taxCalculation       = $objTax;
		$request              = $this->taxCalculation->getRateRequest(null, null, null, 1);
        // echo 'innnnnnnn';die;
        $itemdetails  = '';
        $itemsVisible = $order->getAllVisibleItems();
        $mcin                = 1;
		$invoicesent         = 0;
		$taxamt              = 0;
		$taxpercent          = 0;
		$IGST                = 0;
		$CGST                = 0;
		$SGST                = 0;
		$SIGST                = 0;
		$SCGST                = 0;
        $SSGST                = 0;
        $grandtotal          = 0;
		//~ $taxclassper         = array();
		$itemsArray			 = array();
		$currency_symbol = $this->getCurrentCurrencySymbol();

        foreach ($itemsVisible as $item) {
			//~ echo "<pre>"; print_r($item->getData()); die;
            $productload  = $this->getProductById($item->getProductId());
            $hsn          = $productload->getHsn();
            $sku          = $productload->getSku();
            $rowtotal     = $item->getPrice() * $item->getQtyOrdered();
            $productprice = $priceHelper->currency($item->getPrice(), true, false);
		
			
			$taxClassId = $productload->getTaxClassId();
			$taxpercent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
			//~ $taxclassper[] = $taxpercent;
			$taxamt     = $item->getTaxAmount();
			
			$itemdetails .= '<tr class="invoice-description">';
			$itemdetails .= '<td width="20" class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $mcin . '</td>';
			$itemdetails .= '<td width="300" class="bottom-none" style="text-align:left;border: 2px solid #000; padding: 5px;">' . $item->getName() . '</td>';
            $itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $sku . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $hsn . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . intval($item->getQtyOrdered()) . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $productprice . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . (int)$item->getDiscountPercent() . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $priceHelper->currency($taxamt, true, false) . '</td>';
			
			
			/*****Tax rows calculation******/
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $taxpercent . '</td>';
			if ($state_code == "HR") {
				$IGST = $priceHelper->currency(0, true, false);
				$SGST = $priceHelper->currency($taxamt / 2, true, false);
				$CGST = $priceHelper->currency($taxamt / 2, true, false);
			} else {
				$IGST = $priceHelper->currency($taxamt, true, false);
				$SGST = $priceHelper->currency(0, true, false);
				$CGST = $priceHelper->currency(0, true, false);
			}
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $IGST . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $SGST . '</td>';
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $CGST . '</td>';
			$rowtotal += $taxamt;
			$rowtotal -= $item->getDiscountAmount();
			
			$rowtotalamt = $priceHelper->currency($rowtotal, true, false);;
			
			$itemdetails .= '<td class="bottom-none" style="border: 2px solid #000; padding: 5px;">' . $rowtotalamt . '</td>';
			$itemdetails .= '</tr>';
			
			$grandtotal = $grandtotal + $rowtotal;
	   
            $mcin++;
        }
        $shippingamount      = $order->getShippingAmount();
		$shippingTax = $order->getShippingTaxAmount();
		/*********get shipping tax percent ********/
		// $storels                = Mage::app()->getStore('default');
        // $taxCalculation         = Mage::getModel('tax/calculation');
        // echo 'innnnnnn';die;
        $request                = $this->taxCalculation->getRateRequest(null, null, null, $this->storels);
        $taxRateId              = $this->globalConfig->getValue('tax/classes/shipping_tax_class');
        //taxRateId is the same model id as product tax classes, so you can do this:
        $shippingTaxpercent     = $this->taxCalculation->getRate($request->setProductClassId($taxRateId));
        
        //~ $shippingTaxpercent = max($taxclassper);
        $itemsArray["shipping_amount"] = $shippingamount;
        if($shippingTax > 0){
			if ($state_code == "HR") {
				$SIGST = $priceHelper->currency(0, true, false);
				$SSGST = $priceHelper->currency($shippingTax / 2, true, false);
				$SCGST = $priceHelper->currency($shippingTax / 2, true, false);
			} else {
				$SIGST = $priceHelper->currency($shippingTax, true, false);
				$SSGST = $priceHelper->currency(0, true, false);
				$SCGST = $priceHelper->currency(0, true, false);
			}
			
			$itemsArray["shipping_tax_amount"] = $shippingTax;
			$itemsArray["shipping_tax_percent"] = $shippingTaxpercent;
			$itemsArray["SIGST"] = $SIGST;
			$itemsArray["SSGST"] = $SSGST;
			$itemsArray["SCGST"] = $SCGST;
		}
		else{
			$itemsArray["shipping_tax_amount"] = $priceHelper->currency(0, true, false);
			$itemsArray["shipping_tax_percent"] = 0;
			$itemsArray["SIGST"] = $priceHelper->currency(0, true, false);
			$itemsArray["SSGST"] = $priceHelper->currency(0, true, false);
			$itemsArray["SCGST"] = $priceHelper->currency(0, true, false);
		}
		$grandtotal += $shippingTax + $shippingamount;
		$itemsArray["itemsdetail"] = $itemdetails;
		$itemsArray["grandtotal"] = $grandtotal;
        // echo "<pre>"; print_r($itemsArray); die;
        return $itemsArray;
    }

    /**
     * @param string $region
     * @return string
     */
    public function getRegionCode(string $region)
    {
        $regionCode = $this->collectionFactory->create()
            ->addRegionNameFilter($region)
            ->getFirstItem()
            ->toArray();
        return $regionCode['code'];
    }

     /**
     * @param string $region
     * @return string
     */
    public function getRegionID(string $region)
    {
        $regionID = $this->collectionFactory->create()
            ->addRegionNameFilter($region)
            ->getFirstItem()
            ->toArray();
        return $regionID['region_id'];
    }

    /**
     * Get currency symbol for current locale and currency code
     *
     * @return string
     */    
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    public function getProductById($id)
	{
		return $this->_productRepository->getById($id);
	}

}


