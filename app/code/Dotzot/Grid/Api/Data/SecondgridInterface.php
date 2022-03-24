<?php

namespace Dotzot\Grid\Api\Data;

interface SecondgridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID                =       'entity_id';
    const DOCKET_NO                =        'docket_no';
    const PAYMENT_METHOD           =        'payment_method';
    

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
    public function getDocketno();

   /**
    * Set Title.
    */
    public function setDocketno($docketno);

   /**
    * Get Content.
    *
    * @return varchar
    */
    public function getPaymentmethod();

   /**
    * Set Content.
    */
    public function setPaymentmethod($paymentmethod);

   
}