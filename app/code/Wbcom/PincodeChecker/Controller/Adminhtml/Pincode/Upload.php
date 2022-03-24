<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\Pincode;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * @var bool|\Magento\Framework\View\Result\PageFactory
     */
	protected $resultPageFactory = false;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * Upload constructor.
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
	public function __construct(
        \Magento\Framework\File\Csv $csv,

		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);
        $this->csv = $csv;
        $this->_messageManager = $context->getMessageManager();
		$this->resultPageFactory = $resultPageFactory;
	}

  public function execute()
    {	
        
    }
}
