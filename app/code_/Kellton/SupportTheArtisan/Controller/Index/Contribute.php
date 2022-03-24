<?php 
namespace Kellton\SupportTheArtisan\Controller\Index;
use \Magento\Framework\View\Result\Page;

class Contribute extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
   

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory
		)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultRedirect = $this->_pageFactory->create();
		return $resultRedirect;
	}
}