<?php
/**
 * @author Evince Team
 * @copyright Copyright (c) 2018 Evince (http://DotTrack.com/)
 */

namespace DotTrack\Trackorder\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLED = 'trackorder_config/general/enable';
    protected $_order;
    const XML_PATH_LICENSE_KEY = 'delhivery_lastmile/general/license_key';
   
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->_order = $order;
        parent::__construct($context);
    }
    public function isEnabled()
    {
    return $this->scopeConfig->getValue(self::XML_PATH_ENABLED,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


     public function getTrackorderStatus($order_id)
    {
        $delhivery = array();

        $order = $this->_order->load($order_id);       

        $shipments = $order->getShipmentsCollection();

        foreach ($shipments as $shipment)
        {
            $tracks = $shipment->getTracksCollection();

            $shipid = $shipment->getIncrementId();

            foreach ($tracks as $track)
            {
                $trackingInfos = $track->getData();

                $waybill = $trackingInfos['track_number'];
                $token = $this->scopeConfig->getValue(self::XML_PATH_LICENSE_KEY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $url = "https://track.delhivery.com/api/packages/json/?token=".$token."&order=".$order->getIncrementId()."&waybill=".$waybill;

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL,  $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 60);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: Application/json"));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0 );
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

                $resp = curl_exec($curl);
                
                curl_close($curl);
              
                $resp = json_decode($resp,true);
                $instructions = $resp['ShipmentData'][0]['Shipment']['Status']['Instructions'];
                $StatusDateTime = $resp['ShipmentData'][0]['Shipment']['Status']['StatusDateTime'];
                
                $status = $resp['ShipmentData'][0]['Shipment']['Status']['Status'];

                array_push($delhivery ,array('status' => $status, 'instructions' => $instructions,'shipid' => $shipid,'StatusDateTime'=>$StatusDateTime,'waybill'=>$waybill));

            }
        }
        return $delhivery;
    }

}
