<?php

namespace Kellton\Shipmentdelay\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {
	
	protected $_Collection;
	protected $_Shipment;
	protected $_Order;
	protected $_ScopeConfigInterface;
	protected $_item;
	protected $_shipmentdelay;


    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context,
    	\Kellton\Shipmentdelay\Model\ResourceModel\Shipmentdelay\Collection $ShipmentdelayCollection,
    	\Magento\Sales\Model\Order\Shipment $Shipment,
    	\Magento\Sales\Model\Order  $Order,
    	\Magento\Framework\App\Config\ScopeConfigInterface $ScopeConfigInterface,
    	\Magento\Sales\Model\Order\Item $Item,
    	\Kellton\Shipmentdelay\Model\shipmentdelay $shipmentdelay,
    	 array $data = []
    	) {       
        $this->_Collection             = $ShipmentdelayCollection;
        $this->_Shipment               = $Shipment;
        $this->_Order                  = $Order;
        $this->_ScopeConfigInterface   = $ScopeConfigInterface;
        $this->_item                   = $Item;
        $this->_shipmentdelay          = $shipmentdelay;

        parent::__construct($context, $data);

    }

	public function getshipmentdelayCollection(){

        return $this->_Collection->create()->getCollection();

	}

	public function getshipmentdelay($sid){

        return $this->_shipmentdelay->create()->load($sid);
	}


	public function getScopeconfig(){

		return $this->_ScopeConfigInterface->create();
	}

	public function getorderItem($item_id){

		return $this->_item->create()->load($item_id); 
	}

	public function getshipments($shipmentid){

		return $this->_Shipment->create()->loadByIncrementId($shipmentid);
	}

	public function getorder($orderId){

		return $this->_Order->create()->load($orderId);
	}

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

}