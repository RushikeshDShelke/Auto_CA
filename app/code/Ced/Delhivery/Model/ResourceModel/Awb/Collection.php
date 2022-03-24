<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
	 namespace Ced\Delhivery\Model\ResourceModel\Awb;
	 use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
	 
	class Collection extends AbstractCollection
	{
	    /**
	     * Define model & resource model	     */
	    protected function _construct()
	    {
	        $this->_init(
	            'Ced\Delhivery\Model\Awb',
	            'Ced\Delhivery\Model\ResourceModel\Awb'
	        );
	    }
	}