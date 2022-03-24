<?php
namespace Cminds\Ordercomment\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class SaveDates extends Action{

	protected $resultPageFactory;
    protected $helper;
    protected $storeManager;
    protected $scopeConfig;
    protected $collectionFactory;
    protected $orderFactory;
    protected $orderRepository;
    protected $orderItemRepository;

    public function __construct(
        Context $context,
 		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
 		OrderItemRepositoryInterface $orderItemRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute() {

    	$item_id = $this->getRequest()->getParam('id');
		//$rdate=$_REQUEST['updatereadiness'];
		$rdate = $this->getRequest()->getParam('updatereadiness');
		//$orderid=$_REQUEST['orderid'];
		$orderid = $this->getRequest()->getParam('orderid');
		$orderitemstatus=1;
		//$orderItem = Mage::getModel('sales/order_item')->load($item_id);
		$orderItem = $this->orderItemRepository->get($item_id);
		$orderItem->setOrderitemstatus($orderitemstatus);
		$orderItem->setItemreadinessdate($rdate);
		$orderItem->save();
		//Mage::getSingleton('adminhtml/session')->addSuccess('Date is updated');
		$this->messageManager->addSuccessMessage('Date is updated');
		$url=$this->getUrl('sales/order/view', ['order_id' => $orderid]);
		//Mage::app()->getResponse()->setRedirect($url)->sendResponse(); 
		//$response = Mage::app()->getResponse();
        //$response->clearHeaders()->setRedirect($url)->sendHeadersAndExit();
        $this->getResponse()->setRedirect($url);
	  
    }
}

