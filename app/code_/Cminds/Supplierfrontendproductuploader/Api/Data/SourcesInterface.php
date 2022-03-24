<?php
namespace Cminds\Supplierfrontendproductuploader\Api\Data;

/**
 * Cminds Supplierfrontendproductuploader Sources interface.
 * @api
 * @category Cminds
 * @package  Cminds_MultiUserAccounts
 */

interface SourcesInterface
{
    /**
     * Source statuses.
     */
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * Source entity data keys.
     */
    const ENTITY_ID = 'entity_id';
    const SOURCE_CODE = 'source_code';
    const NAME = 'name';
    const ENABLED = 'enabled';
    const DESCRIPTION = 'description';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
    const COUNTRY_ID = 'country_id';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const CITY = 'city';
    const STREET = 'street';
    const POSTCODE = 'postcode';
    const CONTACT_NAME = 'contact_name';
    const EMAIL = 'email';
    const PHONE = 'phone';
    const FAX = 'fax';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STATUS = 'status';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_ID = 'customer_id';
    const WEBSITE_ID = 'website_id';

    /**
     * Get entity id.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set entity id.
     *
     * @param int $entity_id
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setEntityId($entity_id);

    /**
     * Get source code.
     *
     * @return string
     */
    public function getSourceCode();
    
    /**
     * Set source code.
     *
     * @param string $code
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setSourceCode($code);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set name.
     *
     * @param string $name
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setName($name);

    /**
     * Get enabled.
     *
     * @return int
     */
    public function getEnabled();

    /**
     * Set enabled.
     *
     * @param int $enabled
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setEnabled($enabled);

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description.
     *
     * @param string $description
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setDescription($description);

    /**
     * Get latitude.
     *
     * @return float
     */
    public function getLatitude();

    /**
     * Set latitude.
     *
     * @param float $latitude
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setLatitude($latitude);

    /**
     * Get longitude.
     *
     * @return float
     */
    public function getLongitude();

    /**
     * Set longitude.
     *
     * @param float $longitude
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setLongitude($longitude);

    /**
     * Get country_id.
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country_id.
     *
     * @param string $country_id
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setCountryId($country_id);

    /**
     * Get region_id.
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Set region_id.
     *
     * @param int $region_id
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setRegionId($region_id);

    /**
     * Get region.
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region.
     *
     * @param string $region
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setRegion($region);

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city.
     *
     * @param string $city
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setCity($city);

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet();

    /**
     * Set street.
     *
     * @param string $street
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setStreet($street);

    /**
     * Get postcode.
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode.
     *
     * @param string $postcode
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setPostcode($postcode);

    /**
     * Get contact_name.
     *
     * @return string
     */
    public function getContactName();

    /**
     * Set contact_name.
     *
     * @param string $contact_name
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setContactName($contact_name);

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setEmail($email);

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set phone.
     *
     * @param string $phone
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setPhone($phone);

    /**
     * Get fax.
     *
     * @return string
     */
    public function getFax();

    /**
     * Set fax.
     *
     * @param string $fax
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setFax($fax);

    /**
     * Get created_at.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created_at.
     *
     * @param string $created_at
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setCreatedAt($created_at);

    /**
     * Get updated_at.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated_at.
     *
     * @param string $updated_at
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setUpdatedAt($updated_at);

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status.
     *
     * @param int $status
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setStatus($status);

    /**
     * Get customer_email.
     *
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Set customer_email.
     *
     * @param string $customer_email
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setCustomerEmail($customer_email);

    /**
     * Get customer_id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer_id.
     *
     * @param int $customer_id
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get customer_id.
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website_id.
     *
     * @param int $website_id
     * 
     * @return \Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface
     */
    public function setWebsiteId($website_id);
}
