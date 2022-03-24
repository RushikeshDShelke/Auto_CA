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
interface LocationInterface
{
    /**
     * ID
     * 
     * @var string
     */
    const LOCATION_ID = 'location_id';

    /**
     * Name attribute constant
     * 
     * @var string
     */
    const NAME = 'name';

    /**
     * Address attribute constant
     * 
     * @var string
     */
    const ADDRESS = 'address';

    /**
     * Contact Person attribute constant
     * 
     * @var string
     */
    const CONTACT_PERSON = 'contact_person';

    /**
     * Email attribute constant
     * 
     * @var string
     */
    const EMAIL = 'email';

    /**
     * Phone attribute constant
     * 
     * @var string
     */
    const PHONE = 'phone';

    /**
     * Pin attribute constant
     * 
     * @var string
     */
    const PIN = 'pin';

    /**
     * City attribute constant
     * 
     * @var string
     */
    const CITY = 'city';

    /**
     * State attribute constant
     * 
     * @var string
     */
    const STATE = 'state';

    /**
     * Incoming Center attribute constant
     * 
     * @var string
     */
    const INCOMING_CENTER = 'incoming_center';

    /**
     * Rto Center attribute constant
     * 
     * @var string
     */
    const RTO_CENTER = 'rto_center';

    /**
     * Dto Center attribute constant
     * 
     * @var string
     */
    const DTO_CENTER = 'dto_center';

    /**
     * Status attribute constant
     * 
     * @var string
     */
    const STATUS = 'status';

    /**
     * Expected Package Count attribute constant
     * 
     * @var string
     */
    const EXPECTED_PACKAGE_COUNT = 'expected_package_count';

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
    public function getLocationId();

    /**
     * Set ID
     *
     * @param int $locationId
     * @return LocationInterface
     */
    public function setLocationId($locationId);

    /**
     * Get Name
     *
     * @return mixed
     */
    public function getName();

    /**
     * Set Name
     *
     * @param mixed $name
     * @return LocationInterface
     */
    public function setName($name);

    /**
     * Get Address
     *
     * @return mixed
     */
    public function getAddress();

    /**
     * Set Address
     *
     * @param mixed $address
     * @return LocationInterface
     */
    public function setAddress($address);

    /**
     * Get Contact Person
     *
     * @return mixed
     */
    public function getContactPerson();

    /**
     * Set Contact Person
     *
     * @param mixed $contactPerson
     * @return LocationInterface
     */
    public function setContactPerson($contactPerson);

    /**
     * Get Email
     *
     * @return mixed
     */
    public function getEmail();

    /**
     * Set Email
     *
     * @param mixed $email
     * @return LocationInterface
     */
    public function setEmail($email);

    /**
     * Get Phone
     *
     * @return mixed
     */
    public function getPhone();

    /**
     * Set Phone
     *
     * @param mixed $phone
     * @return LocationInterface
     */
    public function setPhone($phone);

    /**
     * Get Pin
     *
     * @return mixed
     */
    public function getPin();

    /**
     * Set Pin
     *
     * @param mixed $pin
     * @return LocationInterface
     */
    public function setPin($pin);

    /**
     * Get City
     *
     * @return mixed
     */
    public function getCity();

    /**
     * Set City
     *
     * @param mixed $city
     * @return LocationInterface
     */
    public function setCity($city);

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
     * @return LocationInterface
     */
    public function setState($state);

    /**
     * Get Incoming Center
     *
     * @return mixed
     */
    public function getIncomingCenter();

    /**
     * Set Incoming Center
     *
     * @param mixed $incomingCenter
     * @return LocationInterface
     */
    public function setIncomingCenter($incomingCenter);

    /**
     * Get Rto Center
     *
     * @return mixed
     */
    public function getRtoCenter();

    /**
     * Set Rto Center
     *
     * @param mixed $rtoCenter
     * @return LocationInterface
     */
    public function setRtoCenter($rtoCenter);

    /**
     * Get Dto Center
     *
     * @return mixed
     */
    public function getDtoCenter();

    /**
     * Set Dto Center
     *
     * @param mixed $dtoCenter
     * @return LocationInterface
     */
    public function setDtoCenter($dtoCenter);

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
     * @return LocationInterface
     */
    public function setStatus($status);

    /**
     * Get Expected Package Count
     *
     * @return mixed
     */
    public function getExpectedPackageCount();

    /**
     * Set Expected Package Count
     *
     * @param mixed $expectedPackageCount
     * @return LocationInterface
     */
    public function setExpectedPackageCount($expectedPackageCount);
}
