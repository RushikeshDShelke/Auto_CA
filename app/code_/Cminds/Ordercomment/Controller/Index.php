<?php
namespace Cminds\Ordercomment\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;


class Index extends Action{

	protected $resultPageFactory;
    protected $helper;
    protected $storeManager;
    protected $scopeConfig;
    protected $collectionFactory;
    protected $orderFactory;
    protected $orderRepository;

    public function __construct(
        Context $context,
 		\Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute() {

    	$resultPage = $this->resultPageFactory->create();
    	$resultPage->addBreadcrumb(__('Home'), __('Home'));
        $resultPage->addBreadcrumb(
            __('Order comment'),
            __('Order comment')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Order comment'));
      
	 	return $resultPage;
	  
    }
}

