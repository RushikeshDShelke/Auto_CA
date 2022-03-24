<?php


namespace Dotzot\Grid\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID     = 'entity_id';
    const SR            = 'sr';
    const PRODUCT       = 'product';
    const PINCODE       = 'pincode';
    const CITY          = 'city';
    const STATE         = 'state';
    const REGION        = 'region';
    const PREPAID       = 'prepaid';
    const COD           = 'cod';
    const REVERSEPICKUP = 'reversepickup';
    const PICKUP        = 'pickup';
    const SERVICE       = 'service';

   /**
    * Get EntityId.
    *
    * @return int
    */
    public function getEntityId();

   /**
    * Set EntityId.
    */
    public function setEntityId($entityId);

   /**
    * Get Title.
    *
    * @return varchar
    */
    public function getProduct();

   /**
    * Set Title.
    */
    public function setProduct($product);

   /**
    * Get Content.
    *
    * @return varchar
    */
    public function getPincode();

   /**
    * Set Content.
    */
    public function setPincode($pincode);

   /**
    * Get Publish Date.
    *
    * @return varchar
    */
    public function getCity();

   /**
    * Set PublishDate.
    */
    public function setCity($city);

   /**
    * Get IsActive.
    *
    * @return varchar
    */
    public function getState();

   /**
    * Set StartingPrice.
    */
    public function setState($state);

   /**
    * Get UpdateTime.
    *
    * @return varchar
    */
    public function getCod();

   /**
    * Set UpdateTime.
    */
    public function setCod($cod);

   /**
    * Get CreatedAt.
    *
    * @return varchar
    */
    public function getPrepaid();

   /**
    * Set CreatedAt.
    */
    public function setPrepaid($prepaid);
    
    
   /**
    * Get CreatedAt.
    *
    * @return varchar
    */
    public function getReversepickup();

   /**
    * Set CreatedAt.
    */
    public function setReversepickup($reversepickup);
    
    
       /**
    * Get CreatedAt.
    *
    * @return varchar
    */
    public function getPickup();

   /**
    * Set CreatedAt.
    */
    public function setPickup($Pickup);
    
    
}
