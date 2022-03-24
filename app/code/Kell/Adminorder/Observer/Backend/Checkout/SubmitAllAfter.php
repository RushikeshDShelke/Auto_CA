<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kell\Adminorder\Observer\Backend\Checkout;

class SubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
		
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/admin_order_create.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		
        $logger->info('-------- Before--------');
        $logger->info($observer->getEvent()->getOrder()->getStatus());
        $logger->info($observer->getEvent()->getOrder()->getStatusLabel());


        $order = $observer->getEvent()->getOrder();
        $logger->info('Order Id: ' . $order->getId());

        if($observer->getEvent()->getOrder()->getStatus() == "pending_payment" || $observer->getEvent()->getOrder()->getStatus() == "pending"){
            $order->setState("processing")->setStatus("processing");
            $order->addStatusToHistory($order->getStatus(), "Order was created by Admin");
			$order->setIsManually("Yes");
            $order->save();
        }

        $logger->info('--------------- After  --------');
        $logger->info($order->getState());
        $logger->info($order->getStatus());
        $logger->info('-------- End--------');
    }

}

