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
 * @method \Delhivery\Lastmile\Model\ResourceModel\Location _getResource()
 * @method \Delhivery\Lastmile\Model\ResourceModel\Location getResource()
 */
class Location extends \Magento\Framework\Model\AbstractModel implements \Delhivery\Lastmile\Api\Data\LocationInterface
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'delhivery_lastmile_location';

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
    protected $_eventPrefix = 'delhivery_lastmile_location';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'location';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Delhivery\Lastmile\Model\ResourceModel\Location::class);
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
     * Get Manage Location id
     *
     * @return array
     */
    public function getLocationId()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::LOCATION_ID);
    }

    /**
     * set Manage Location id
     *
     * @param int $locationId
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setLocationId($locationId)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::LOCATION_ID, $locationId);
    }

    /**
     * set Name
     *
     * @param mixed $name
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setName($name)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::NAME, $name);
    }

    /**
     * get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::NAME);
    }

    /**
     * set Address
     *
     * @param mixed $address
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setAddress($address)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::ADDRESS, $address);
    }

    /**
     * get Address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::ADDRESS);
    }

    /**
     * set Contact Person
     *
     * @param mixed $contactPerson
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setContactPerson($contactPerson)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::CONTACT_PERSON, $contactPerson);
    }

    /**
     * get Contact Person
     *
     * @return string
     */
    public function getContactPerson()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::CONTACT_PERSON);
    }

    /**
     * set Email
     *
     * @param mixed $email
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setEmail($email)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::EMAIL, $email);
    }

    /**
     * get Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::EMAIL);
    }

    /**
     * set Phone
     *
     * @param mixed $phone
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setPhone($phone)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::PHONE, $phone);
    }

    /**
     * get Phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::PHONE);
    }

    /**
     * set Pin
     *
     * @param mixed $pin
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setPin($pin)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::PIN, $pin);
    }

    /**
     * get Pin
     *
     * @return string
     */
    public function getPin()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::PIN);
    }

    /**
     * set City
     *
     * @param mixed $city
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setCity($city)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::CITY, $city);
    }

    /**
     * get City
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::CITY);
    }

    /**
     * set State
     *
     * @param mixed $state
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setState($state)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::STATE, $state);
    }

    /**
     * get State
     *
     * @return string
     */
    public function getState()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::STATE);
    }

    /**
     * set Incoming Center
     *
     * @param mixed $incomingCenter
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setIncomingCenter($incomingCenter)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::INCOMING_CENTER, $incomingCenter);
    }

    /**
     * get Incoming Center
     *
     * @return string
     */
    public function getIncomingCenter()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::INCOMING_CENTER);
    }

    /**
     * set Rto Center
     *
     * @param mixed $rtoCenter
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setRtoCenter($rtoCenter)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::RTO_CENTER, $rtoCenter);
    }

    /**
     * get Rto Center
     *
     * @return string
     */
    public function getRtoCenter()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::RTO_CENTER);
    }

    /**
     * set Dto Center
     *
     * @param mixed $dtoCenter
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setDtoCenter($dtoCenter)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::DTO_CENTER, $dtoCenter);
    }

    /**
     * get Dto Center
     *
     * @return string
     */
    public function getDtoCenter()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::DTO_CENTER);
    }

    /**
     * set Status
     *
     * @param mixed $status
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setStatus($status)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::STATUS, $status);
    }

    /**
     * get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::STATUS);
    }

    /**
     * set Expected Package Count
     *
     * @param mixed $expectedPackageCount
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     */
    public function setExpectedPackageCount($expectedPackageCount)
    {
        return $this->setData(\Delhivery\Lastmile\Api\Data\LocationInterface::EXPECTED_PACKAGE_COUNT, $expectedPackageCount);
    }

    /**
     * get Expected Package Count
     *
     * @return string
     */
    public function getExpectedPackageCount()
    {
        return $this->getData(\Delhivery\Lastmile\Api\Data\LocationInterface::EXPECTED_PACKAGE_COUNT);
    }
}
