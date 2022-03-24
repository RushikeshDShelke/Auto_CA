<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Model;

/**
 * @method \Delhivery\Lastmile\Model\ResourceModel\Awb _getResource()
 * @method \Delhivery\Lastmile\Model\ResourceModel\Awb getResource()
 */
class Awb extends \Magento\Framework\Model\AbstractModel implements \Delhivery\Lastmile\Api\Data\AwbInterface
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'delhivery_lastmile_awb';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'delhivery_lastmile_awb';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'awb';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Delhivery\Lastmile\Model\ResourceModel\Awb::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Manage AWB id
     *
     * @return array
     */
    public function getAwbId()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::AWB_ID);
    }

    /**
     * set Manage AWB id
     *
     * @param int $awbId
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setAwbId($awbId)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::AWB_ID, $awbId);
    }

    /**
     * set AWB
     *
     * @param mixed $awb
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setAwb($awb)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::AWB, $awb);
    }

    /**
     * get AWB
     *
     * @return string
     */
    public function getAwb()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::AWB);
    }

    /**
     * set Shipment Id
     *
     * @param mixed $shipmentId
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setShipmentId($shipmentId)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_ID, $shipmentId);
    }

    /**
     * get Shipment Id
     *
     * @return string
     */
    public function getShipmentId()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_ID);
    }

    /**
     * set Shipment To
     *
     * @param mixed $shipmentTo
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setShipmentTo($shipmentTo)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_TO, $shipmentTo);
    }

    /**
     * get Shipment To
     *
     * @return string
     */
    public function getShipmentTo()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_TO);
    }

    /**
     * set State
     *
     * @param mixed $state
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setState($state)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATE, $state);
    }

    /**
     * get State
     *
     * @return string
     */
    public function getState()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATE);
    }

    /**
     * set Status
     *
     * @param mixed $status
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setStatus($status)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATUS, $status);
    }

    /**
     * get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATUS);
    }

    /**
     * set Status Type
     *
     * @param mixed $statusType
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setStatusType($statusType)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATUS_TYPE, $statusType);
    }

    /**
     * get Status Type
     *
     * @return string
     */
    public function getStatusType()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATUS_TYPE);
    }

    /**
     * set Pickup Location Id
     *
     * @param mixed $pickupLocationId
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setPickupLocationId($pickupLocationId)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::PICKUP_LOCATION_ID, $pickupLocationId);
    }

    /**
     * get Pickup Location Id
     *
     * @return string
     */
    public function getPickupLocationId()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::PICKUP_LOCATION_ID);
    }

    /**
     * set Return Address
     *
     * @param mixed $returnAddress
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setReturnAddress($returnAddress)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::RETURN_ADDRESS, $returnAddress);
    }

    /**
     * get Return Address
     *
     * @return string
     */
    public function getReturnAddress()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::RETURN_ADDRESS);
    }

    /**
     * set Shipment Length
     *
     * @param mixed $shipmentLength
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setShipmentLength($shipmentLength)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_LENGTH, $shipmentLength);
    }

    /**
     * get Shipment Length
     *
     * @return string
     */
    public function getShipmentLength()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_LENGTH);
    }

    /**
     * set Shipment Width
     *
     * @param mixed $shipmentWidth
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setShipmentWidth($shipmentWidth)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_WIDTH, $shipmentWidth);
    }

    /**
     * get Shipment Width
     *
     * @return string
     */
    public function getShipmentWidth()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_WIDTH);
    }

    /**
     * set Shipment Height
     *
     * @param mixed $shipmentHeight
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setShipmentHeight($shipmentHeight)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_HEIGHT, $shipmentHeight);
    }

    /**
     * get Shipment Height
     *
     * @return string
     */
    public function getShipmentHeight()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::SHIPMENT_HEIGHT);
    }

    /**
     * set Status Date Time
     *
     * @param mixed $statusDateTime
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setStatusDateTime($statusDateTime)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATUS_DATE_TIME, $statusDateTime);
    }

    /**
     * get Status Date Time
     *
     * @return string
     */
    public function getStatusDateTime()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::STATUS_DATE_TIME);
    }

    /**
     * set Upl
     *
     * @param mixed $upl
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setUpl($upl)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::UPL, $upl);
    }

    /**
     * get Upl
     *
     * @return string
     */
    public function getUpl()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::UPL);
    }

    /**
     * set Order Id
     *
     * @param mixed $orderid
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     */
    public function setOrderid($orderid)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\AwbInterface::ORDERID, $orderid);
    }

    /**
     * get Order Id
     *
     * @return string
     */
    public function getOrderid()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\AwbInterface::ORDERID);
    }
}
