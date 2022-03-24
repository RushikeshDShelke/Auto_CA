<?php

namespace Cminds\Supplierfrontendproductuploader\Observer;

use Cminds\Supplierfrontendproductuploader\Helper\Email as HelperEmail;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OnOrderPlaced implements ObserverInterface
{
    private $helperEmail;
    private $productFactory;
    private $customerFactory;

    public function __construct(
        HelperEmail $helperEmail,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory
    ) {
        $this->helperEmail = $helperEmail;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
    }

    public function execute(Observer $observer)
    {
        //$order = $observer->getEvent()->getOrder();
	$shipment  = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();	
	$orderId = $order->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();

        $items = $order->getAllItems();

        $orderdate = $order->getCreatedAt();
        $cdate = new \DateTime($orderdate);
        $cdate->add(new \DateInterval('P8D'));
        $deliverydate=$cdate->format('d-m-Y');

        $orderstatus = $order->getStatus();
        $Incrementid = $order->getIncrementId();


        $itemsBySupplierId = [];

        foreach ($items as $item) {
            $product = $this->productFactory->create()
                ->load($item->getProductId());

            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();            
        
            $merchantId = "MCID";
            $artisannumber=$merchantId.'-'.$Incrementid;

            if ($product->getData('creator_id') === null) {
                continue;
            }

            $itemData = [];
            $itemData['name'] = $item->getName();
            $itemData['size'] = $product->getSize();
            $itemData['image'] = '<img src="'.$productImageUrl.'" height="200px" />';
            $itemData['price'] = $item->getPrice();
            $itemData['sku'] = $item->getSku();
            $itemData['supplier_id'] = $product->getData('creator_id');
            $itemData['id'] = $item->getProductId();
            $itemData['qty'] = $item->getQtyToInvoice();
            $itemData['qty_ordered'] = $item->getQtyOrdered();
            $itemData['artisannumber'] = $artisannumber;
            $itemData['ordstatus'] = $orderstatus;
            $itemData['orderdate'] = $orderdate;
            $itemData['productimage'] = $product->getImageUrl();
            $itemData['productsize'] = $product->getSize();
            $itemData['deliverydate'] = $deliverydate;

            if ($order->getShippingAddress()) {
                $itemData['firstname'] = $order->getShippingAddress()->getFirstname();
                $itemData['lastname'] = $order->getShippingAddress()->getLastname();
                $itemData['street'] = $order->getShippingAddress()->getStreet();
                $itemData['city'] = $order->getShippingAddress()->getCity();
                $itemData['email'] = $order->getShippingAddress()->getEmail();
                $itemData['postcode'] = $order->getShippingAddress()->getPostcode();
                $itemData['region'] = $order->getShippingAddress()->getRegion();
                $itemData['getCountryId'] = $order->getShippingAddress()->getCountryId();
            } else {
                $itemData['firstname'] = null;
                $itemData['lastname'] = null;
                $itemData['street'] = null;
                $itemData['city'] = null;
                $itemData['email'] = null;
                $itemData['postcode'] = null;
                $itemData['region'] = null;
                $itemData['getCountryId'] = null;
            }

            $itemData['order_id'] = $orderId;
            $itemData['product_name'] = $product->getName();
            $itemData['product_url'] = $product->getProductUrl();


            $itemsBySupplierId[$itemData['supplier_id']][] = $itemData;
        }

        foreach ($itemsBySupplierId as $supplierId => $items) {
            $supplier = $this->customerFactory->create()
                ->load($supplierId);


 

            $this->helperEmail->newOrderEmail($supplier, $items);
        }

        return $this;
    }
}
