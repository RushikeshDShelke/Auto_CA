<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Delhivery\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesShipmentTrack implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
     protected $_registry = null;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $quote
     */
   public function __construct (        
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager

    ) {    
        $this->_objectManager=$objectManager;

        $this->_registry = $registry;
    }
 

    /**
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		
            $track = $observer->getEvent()->getTrack();
            $order = $track->getShipment()->getOrder();
            $shipment = $observer->getEvent()->getShipment();

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $logger->info("Now in Send of senderbuilder");
            $shippingMethod = $order->getShippingMethod(); // String in format of 'carrier_method'
            // Process only Delhivery Lastmile methods
            if (!$shippingMethod || $track->getCarrierCode() != 'delhivery') {
                return;
            }
            
            $model = $this->_objectManager->create('Ced\Delhivery\Model\Awb');
            $model = $model->load($track->getNumber(),'awb');
            $data = array();
            $data['state'] = 1;
            $data['status'] = "InTransit";
            $data['awb'] = $track->getNumber();
            $data['orderid'] = $order->getId();
            $data['shipment_to'] = $order->getShippingAddress()->getName();
            $data['shipment_id'] = $track->getShipment()->getIncrementId();
            $data['created_time'] = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d H:i:s');
            $data['update_time'] = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d H:i:s');
            $model->addData($data);        
            $model->save();
            
       }
  }
