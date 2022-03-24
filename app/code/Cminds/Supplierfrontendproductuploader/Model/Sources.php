<?php

namespace Cminds\Supplierfrontendproductuploader\Model;

use Magento\Framework\Model\AbstractModel;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface;

class Sources extends AbstractModel implements SourcesInterface
{
    protected function _construct()
    {
        $this->_init(
            \Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Sources::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        return $this->_getData(self::ENTITY_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function setEntityId($entity_id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceCode()
    {
        return $this->_getData(self::SOURCE_CODE);
    }
    /**
     * {@inheritdoc}
     */
    public function setSourceCode($code)
    {
        return $this->setData(self::SOURCE_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_getData(self::NAME);
    }
    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->_getData(self::ENABLED);
    }
    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        return $this->setData(self::ENABLED, $enabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_getData(self::DESCRIPTION);
    }
    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getLatitude()
    {
        return $this->_getData(self::LATITUDE);
    }
    /**
     * {@inheritdoc}
     */
    public function setLatitude($latitude)
    {
        return $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * {@inheritdoc}
     */
    public function getLongitude()
    {
        return $this->_getData(self::LONGITUDE);
    }
    /**
     * {@inheritdoc}
     */
    public function setLongitude($longitude)
    {
        return $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId()
    {
        return $this->_getData(self::COUNTRY_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function setCountryId($country_id)
    {
        return $this->setData(self::COUNTRY_ID, $country_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId()
    {
        return $this->_getData(self::REGION_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function setRegionId($region_id)
    {
        return $this->setData(self::REGION_ID, $region_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->_getData(self::REGION);
    }
    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_getData(self::CITY);
    }
    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreet()
    {
        return $this->_getData(self::STREET);
    }
    /**
     * {@inheritdoc}
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostcode()
    {
        return $this->_getData(self::POSTCODE);
    }
    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactName()
    {
        return $this->_getData(self::CONTACT_NAME);
    }
    /**
     * {@inheritdoc}
     */
    public function setContactName($contact_name)
    {
        return $this->setData(self::CONTACT_NAME, $contact_name);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_getData(self::EMAIL);
    }
    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone()
    {
        return $this->_getData(self::PHONE);
    }
    /**
     * {@inheritdoc}
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * {@inheritdoc}
     */
    public function getFax()
    {
        return $this->_getData(self::FAX);
    }
    /**
     * {@inheritdoc}
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }
    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($created_at)
    {
        return $this->setData(self::CREATED_AT, $created_at);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }
    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updated_at)
    {
        return $this->setData(self::UPDATED_AT, $updated_at);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }
    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->_getData(self::CUSTOMER_EMAIL);
    }
    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($customer_email)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customer_email);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(self::CUSTOMER_ID, $customer_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->_getData(self::WEBSITE_ID);
    }
    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($website_id)
    {
        return $this->setData(self::WEBSITE_ID, $website_id);
    }
}
