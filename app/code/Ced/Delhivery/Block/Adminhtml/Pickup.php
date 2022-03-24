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

namespace Ced\Delhivery\Block\Adminhtml;

class Pickup extends \Magento\Backend\Block\Template
{
	
    protected $_objectManager;
    protected $_scopConfig;
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
    	$this->_objectmanager=$objectInterface;
        $this->_scopConfig=$scopeConfig;
        parent::__construct($context, $data);
    }

    public function getWarehouse(){
        $warehousename = $this->_scopConfig->getValue('carriers/delhivery/client_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        return $warehousename;
    }
 
   
}