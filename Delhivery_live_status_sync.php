<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
$orderData = $objectManager->get('Magento\Sales\Model\Order')->getCollection();
$statuses = array("complete");
$filterOrders = $orderData->addFieldToFilter('state',
                ['in' => $statuses]
	);
if(count($filterOrders))
{
	//echo "<pre>"; echo count($filterOrders); die;print_r($filterOrders->getData()); die;
	foreach($filterOrders as $order)
	{
		$tracksCollection = $order->getTracksCollection();
		foreach ($tracksCollection->getItems() as $track) {
			$trackNumbers[] = $track->getTrackNumber();
			$url = "https://reqbin.com/echo";
			$url = "https://track.delhivery.com/api/v1/packages/json/?waybill=".$track->getTrackNumber()."&token=36f483d43e0ac389b6f7ad727b0393e3361a284e";
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			//for debug only!
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$resp = curl_exec($curl);
			curl_close($curl);
			//var_dump($resp);
			$json_to_arr = json_decode($resp,true);
			//print_r($json_to_arr);
			//echo "<pre>";
			if(array_key_exists("ShipmentData",$json_to_arr))
			{
			$currentStatus = $json_to_arr['ShipmentData'][0]['Shipment']['Status']['Status'];
			//echo $order->getIncrementId()."+++".$order->getStatusLabel();
			if($currentStatus != $order->getStatusLabel())
			{
				if(($currentStatus == 'In Transit') || ($currentStatus == 'Pending'))
				{
					$order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE, true);
    					$order->setStatus("delhivery_intransit");
    					$order->addStatusToHistory($order->getStatus(), 'Live order status changed according to the Delhivery.');
					$order->save();
					echo $order->getIncrementId()." \n";
				}
				if(($currentStatus == 'Dispatched'))
                                {
                                        $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE, true);
                                        $order->setStatus("dispatched");
                                        $order->addStatusToHistory($order->getStatus(), 'Live order status changed according to the Delhivery.');
                                        $order->save();
                                        echo $order->getIncrementId()." \n";
                                }
				if(($currentStatus == 'Delivered'))
                                {
                                        $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE, true);
                                        $order->setStatus("dehivery_delivered");
                                        $order->addStatusToHistory($order->getStatus(), 'Live order status changed according to the Delhivery.');
                                        $order->save();
                                        echo $order->getIncrementId()." \n";
                                }
			}
			}
		}
		//print_r($trackNumbers); die;

	}
}

die;
?>
