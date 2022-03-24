<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$eventManager = $obj->get("\Magento\Framework\Event\ManagerInterface");
$pendingOrdersCollection = $obj->get("\Magento\Sales\Model\Order")->getCollection()->addFieldToFilter('status', array('eq' => 'pending_payment'));
//$canceledOrdersCollection = $obj->get("\Magento\Sales\Model\Order")->getCollection()->addFieldToFilter('status', array('eq' => 'canceled'));

$to = date("Y-m-d h:i:s"); // current date
$totime = strtotime("-15 minutes", strtotime($to));
$tofinal = date('Y-m-d h:i:s', $totime); // 15 before
$from = strtotime('-1 day', strtotime($to));
$from = date('Y-m-d h:i:s', $from); // 2 days before
$pendingOrdersCollection->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$tofinal));
$orderList = $pendingOrdersCollection->getData();
print_r($orderList);
if($orderList)
{
        foreach($orderList as $singleorder)
	{
		$order = $obj->create('Magento\Sales\Model\Order')->load($singleorder['entity_id']);
		$order->registerCancellation("Order Canceled from Cron after 15 mins")->save();
		$eventManager->dispatch('order_cancel_after', ['order' => $order]);
        } 
}
?>

