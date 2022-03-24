<?php

namespace Dotzot\Grid\Model;

use Dotzot\Grid\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_grid_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_grid_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_grid_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Dotzot\Grid\Model\ResourceModel\Grid');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getProduct()
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * Set Title.
     */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /**
     * Get getContent.
     *
     * @return varchar
     */
    public function getPincode()
    {
        return $this->getData(self::PINCODE);
    }

    /**
     * Set Content.
     */
    public function setPincode($pincode)
    {
        return $this->setData(self::PINCODE, $pincode);
    }

    /**
     * Get city.
     *
     * @return varchar
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Set city.
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get State.
     *
     * @return varchar
     */
    public function getState()
    {
        return $this->getData(self::STATE);
    }

    /**
     * Set State.
     */
    public function setState($State)
    {
        return $this->setData(self::STATE, $State);
    }

    /**
     * Get Region.
     *
     * @return varchar
     */
    public function getCod()
    {
        return $this->getData(self::COD);
    }

    /**
     * Set Region.
     */
    public function setCod($cod)
    {
        return $this->setData(self::COD, $cod);
    }

    /**
     * Get Prepaid.
     *
     * @return varchar
     */
    public function getPrepaid()
    {
        return $this->getData(self::PREPAID);
    }

    /**
     * Set Prepaid.
     */
    public function setPrepaid($Prepaid)
    {
        return $this->setData(self::PREPAID, $Prepaid);
    }
    
    /**
     * Get Prepaid.
     *
     * @return varchar
     */
    public function getReversepickup()
    {
        return $this->getData(self::REVERSEPICKUP);
    }

    /**
     * Set Prepaid.
     */
    public function setReversepickup($Reversepickup)
    {
        return $this->setData(self::REVERSEPICKUP, $Reversepickup);
    }
    
    
    /**
     * Get Prepaid.
     *
     * @return varchar
     */
    public function getPickup()
    {
        return $this->getData(self::Pickup);
    }

    /**
     * Set Prepaid.
     */
    public function setPickup($Pickup)
    {
        return $this->setData(self::Pickup, $Pickup);
    }
}
