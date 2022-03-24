<?php

namespace Kellton\Smsbulk\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {
	
	protected $_Shipment;
	protected $_Order;	
	protected $_ItemFactory;
	protected $_product;

    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context,    	
    	\Magento\Sales\Model\Order\Shipment $Shipment,
    	 \Magento\Sales\Model\OrderRepository $orderRepository,
    	\Magento\Sales\Model\Order\ItemFactory $ItemFactory,
    	\Magento\Catalog\Model\ProductFactory $product,
    	
    	 array $data = []
    	) {       
        
        $this->_Shipment      = $Shipment;
        $this->_Order         = $orderRepository;
        $this->_ItemFactory   = $ItemFactory;
        $this->_product       = $product;        

        parent::__construct($context, $data);

    } 
	

	public function getorderItem($item_id){

		return $this->_item->create()->load($item_id); 
	}

	public function getshipments($shipid){

		return $this->_Shipment->create()->loadByIncrementId($shipid);
	}

	public function getorder($orderId){

		return $this->_Order->get($orderId);
	}

	public function getProduct($id){

		return $this->_product->create()->load($id);
	}

    public function getProductItem($id){

		return $this->_ItemFactory->create()->load($id);
	}
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

}