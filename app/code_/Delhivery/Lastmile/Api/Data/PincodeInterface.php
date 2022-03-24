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
interface PincodeInterface
{
    /**
     * ID
     * 
     * @var string
     */
    const PINCODE_ID = 'pincode_id';

    /**
     * District attribute constant
     * 
     * @var string
     */
    const DISTRICT = 'district';

    /**
     * Pincode attribute constant
     * 
     * @var string
     */
    const PIN = 'pin';

    /**
     * Pre Paid attribute constant
     * 
     * @var string
     */
    const PRE_PAID = 'pre_paid';

    /**
     * Cash attribute constant
     * 
     * @var string
     */
    const CASH = 'cash';

    /**
     * Pickup attribute constant
     * 
     * @var string
     */
    const PICKUP = 'pickup';

    /**
     * COD attribute constant
     * 
     * @var string
     */
    const COD = 'cod';

    /**
     * State Code attribute constant
     * 
     * @var string
     */
    const STATE_CODE = 'state_code';

    /**
     * Status attribute constant
     * 
     * @var string
     */
    const STATUS = 'status';

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
    public function getPincodeId();

    /**
     * Set ID
     *
     * @param int $pincodeId
     * @return PincodeInterface
     */
    public function setPincodeId($pincodeId);

    /**
     * Get District
     *
     * @return mixed
     */
    public function getDistrict();

    /**
     * Set District
     *
     * @param mixed $district
     * @return PincodeInterface
     */
    public function setDistrict($district);

    /**
     * Get Pincode
     *
     * @return mixed
     */
    public function getPin();

    /**
     * Set Pincode
     *
     * @param mixed $pin
     * @return PincodeInterface
     */
    public function setPin($pin);

    /**
     * Get Pre Paid
     *
     * @return mixed
     */
    public function getPrePaid();

    /**
     * Set Pre Paid
     *
     * @param mixed $prePaid
     * @return PincodeInterface
     */
    public function setPrePaid($prePaid);

    /**
     * Get Cash
     *
     * @return mixed
     */
    public function getCash();

    /**
     * Set Cash
     *
     * @param mixed $cash
     * @return PincodeInterface
     */
    public function setCash($cash);

    /**
     * Get Pickup
     *
     * @return mixed
     */
    public function getPickup();

    /**
     * Set Pickup
     *
     * @param mixed $pickup
     * @return PincodeInterface
     */
    public function setPickup($pickup);

    /**
     * Get COD
     *
     * @return mixed
     */
    public function getCod();

    /**
     * Set COD
     *
     * @param mixed $cod
     * @return PincodeInterface
     */
    public function setCod($cod);

    /**
     * Get State Code
     *
     * @return mixed
     */
    public function getStateCode();

    /**
     * Set State Code
     *
     * @param mixed $stateCode
     * @return PincodeInterface
     */
    public function setStateCode($stateCode);

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
     * @return PincodeInterface
     */
    public function setStatus($status);
}
