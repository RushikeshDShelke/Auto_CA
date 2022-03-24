<?php

namespace Dotzot\Grid\Model;

use Dotzot\Grid\Api\Data\SecondgridInterface;

class Secondgrid extends \Magento\Framework\Model\AbstractModel implements SecondgridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_secondgrid_records';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_secondgrid_records';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_secondgrid_records';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Dotzot\Grid\Model\ResourceModel\Secondgrid');
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
    public function getDocketno()
    {
        return $this->getData(self::DOCKET_NO);
    }
    
     public function setDocketno($docketno)
    {
        return $this->setData(self::DOCKET_NO, $docketno);
    }
    
     public function getPaymentmethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * Set Title.
     */
    public function setPaymentmethod($paymentmethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentmethod);
    }
    
        }