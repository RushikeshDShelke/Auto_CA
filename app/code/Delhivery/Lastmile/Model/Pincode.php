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
 * @method \Delhivery\Lastmile\Model\ResourceModel\Pincode _getResource()
 * @method \Delhivery\Lastmile\Model\ResourceModel\Pincode getResource()
 */
class Pincode extends \Magento\Framework\Model\AbstractModel implements \Delhivery\Lastmile\Api\Data\PincodeInterface
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'delhivery_lastmile_pincode';

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
    protected $_eventPrefix = 'delhivery_lastmile_pincode';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'pincode';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Delhivery\Lastmile\Model\ResourceModel\Pincode::class);
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
     * Get Manage Pincode id
     *
     * @return array
     */
    public function getPincodeId()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PINCODE_ID);
    }

    /**
     * set Manage Pincode id
     *
     * @param int $pincodeId
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setPincodeId($pincodeId)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PINCODE_ID, $pincodeId);
    }

    /**
     * set District
     *
     * @param mixed $district
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setDistrict($district)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::DISTRICT, $district);
    }

    /**
     * get District
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::DISTRICT);
    }

    /**
     * set Pincode
     *
     * @param mixed $pin
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setPin($pin)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PIN, $pin);
    }

    /**
     * get Pincode
     *
     * @return string
     */
    public function getPin()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PIN);
    }

    /**
     * set Pre Paid
     *
     * @param mixed $prePaid
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setPrePaid($prePaid)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PRE_PAID, $prePaid);
    }

    /**
     * get Pre Paid
     *
     * @return string
     */
    public function getPrePaid()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PRE_PAID);
    }

    /**
     * set Cash
     *
     * @param mixed $cash
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setCash($cash)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::CASH, $cash);
    }

    /**
     * get Cash
     *
     * @return string
     */
    public function getCash()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::CASH);
    }

    /**
     * set Pickup
     *
     * @param mixed $pickup
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setPickup($pickup)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PICKUP, $pickup);
    }

    /**
     * get Pickup
     *
     * @return string
     */
    public function getPickup()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::PICKUP);
    }

    /**
     * set COD
     *
     * @param mixed $cod
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setCod($cod)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::COD, $cod);
    }

    /**
     * get COD
     *
     * @return string
     */
    public function getCod()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::COD);
    }

    /**
     * set State Code
     *
     * @param mixed $stateCode
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setStateCode($stateCode)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::STATE_CODE, $stateCode);
    }

    /**
     * get State Code
     *
     * @return string
     */
    public function getStateCode()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::STATE_CODE);
    }

    /**
     * set Status
     *
     * @param mixed $status
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     */
    public function setStatus($status)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\PincodeInterface::STATUS, $status);
    }

    /**
     * get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\PincodeInterface::STATUS);
    }
}
