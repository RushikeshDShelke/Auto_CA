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
namespace Delhivery\Lastmile\Api\Data;

/**
 * @api
 */
interface AwbInterface
{
    /**
     * ID
     * 
     * @var string
     */
    const AWB_ID = 'awb_id';

    /**
     * AWB attribute constant
     * 
     * @var string
     */
    const AWB = 'awb';

    /**
     * Shipment Id attribute constant
     * 
     * @var string
     */
    const SHIPMENT_ID = 'shipment_id';

    /**
     * Shipment To attribute constant
     * 
     * @var string
     */
    const SHIPMENT_TO = 'shipment_to';

    /**
     * State attribute constant
     * 
     * @var string
     */
    const STATE = 'state';

    /**
     * Status attribute constant
     * 
     * @var string
     */
    const STATUS = 'status';

    /**
     * Status Type attribute constant
     * 
     * @var string
     */
    const STATUS_TYPE = 'status_type';

    /**
     * Pickup Location Id attribute constant
     * 
     * @var string
     */
    const PICKUP_LOCATION_ID = 'pickup_location_id';

    /**
     * Return Address attribute constant
     * 
     * @var string
     */
    const RETURN_ADDRESS = 'return_address';

    /**
     * Shipment Length attribute constant
     * 
     * @var string
     */
    const SHIPMENT_LENGTH = 'shipment_length';

    /**
     * Shipment Width attribute constant
     * 
     * @var string
     */
    const SHIPMENT_WIDTH = 'shipment_width';

    /**
     * Shipment Height attribute constant
     * 
     * @var string
     */
    const SHIPMENT_HEIGHT = 'shipment_height';

    /**
     * Status Date Time attribute constant
     * 
     * @var string
     */
    const STATUS_DATE_TIME = 'status_date_time';

    /**
     * Upl attribute constant
     * 
     * @var string
     */
    const UPL = 'upl';

    /**
     * Order Id attribute constant
     * 
     * @var string
     */
    const ORDERID = 'orderid';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getAwbId();

    /**
     * Set ID
     *
     * @param int $awbId
     * @return AwbInterface
     */
    public function setAwbId($awbId);

    /**
     * Get AWB
     *
     * @return mixed
     */
    public function getAwb();

    /**
     * Set AWB
     *
     * @param mixed $awb
     * @return AwbInterface
     */
    public function setAwb($awb);

    /**
     * Get Shipment Id
     *
     * @return mixed
     */
    public function getShipmentId();

    /**
     * Set Shipment Id
     *
     * @param mixed $shipmentId
     * @return AwbInterface
     */
    public function setShipmentId($shipmentId);

    /**
     * Get Shipment To
     *
     * @return mixed
     */
    public function getShipmentTo();

    /**
     * Set Shipment To
     *
     * @param mixed $shipmentTo
     * @return AwbInterface
     */
    public function setShipmentTo($shipmentTo);

    /**
     * Get State
     *
     * @return mixed
     */
    public function getState();

    /**
     * Set State
     *
     * @param mixed $state
     * @return AwbInterface
     */
    public function setState($state);

    /**
     * Get Status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param mixed $status
     * @return AwbInterface
     */
    public function setStatus($status);

    /**
     * Get Status Type
     *
     * @return mixed
     */
    public function getStatusType();

    /**
     * Set Status Type
     *
     * @param mixed $statusType
     * @return AwbInterface
     */
    public function setStatusType($statusType);

    /**
     * Get Pickup Location Id
     *
     * @return mixed
     */
    public function getPickupLocationId();

    /**
     * Set Pickup Location Id
     *
     * @param mixed $pickupLocationId
     * @return AwbInterface
     */
    public function setPickupLocationId($pickupLocationId);

    /**
     * Get Return Address
     *
     * @return mixed
     */
    public function getReturnAddress();

    /**
     * Set Return Address
     *
     * @param mixed $returnAddress
     * @return AwbInterface
     */
    public function setReturnAddress($returnAddress);

    /**
     * Get Shipment Length
     *
     * @return mixed
     */
    public function getShipmentLength();

    /**
     * Set Shipment Length
     *
     * @param mixed $shipmentLength
     * @return AwbInterface
     */
    public function setShipmentLength($shipmentLength);

    /**
     * Get Shipment Width
     *
     * @return mixed
     */
    public function getShipmentWidth();

    /**
     * Set Shipment Width
     *
     * @param mixed $shipmentWidth
     * @return AwbInterface
     */
    public function setShipmentWidth($shipmentWidth);

    /**
     * Get Shipment Height
     *
     * @return mixed
     */
    public function getShipmentHeight();

    /**
     * Set Shipment Height
     *
     * @param mixed $shipmentHeight
     * @return AwbInterface
     */
    public function setShipmentHeight($shipmentHeight);

    /**
     * Get Status Date Time
     *
     * @return mixed
     */
    public function getStatusDateTime();

    /**
     * Set Status Date Time
     *
     * @param mixed $statusDateTime
     * @return AwbInterface
     */
    public function setStatusDateTime($statusDateTime);

    /**
     * Get Upl
     *
     * @return mixed
     */
    public function getUpl();

    /**
     * Set Upl
     *
     * @param mixed $upl
     * @return AwbInterface
     */
    public function setUpl($upl);

    /**
     * Get Order Id
     *
     * @return mixed
     */
    public function getOrderid();

    /**
     * Set Order Id
     *
     * @param mixed $orderid
     * @return AwbInterface
     */
    public function setOrderid($orderid);
}
